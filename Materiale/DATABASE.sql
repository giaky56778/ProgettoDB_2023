--create database progetto;

create role segretario;
create role docente;
create role studente;
create role visitatore;
create user accesso with encrypted password 'progettodatabase';
grant visitatore to accesso;

create table if not exists Utenti(
    email varchar(120) primary key,
    nome varchar(50) not null,
    cognome varchar(50) not null,
    password text not null,
    cf varchar(16) unique,
    tipologia varchar(20) not null,
    constraint codice_fiscale_ER check ( cf ~ '^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$' ),
    constraint tipologia_check check (tipologia in ('studente','docente','segretario')),
    constraint email_check check ( email ~ '^([a-z]+)[.]([a-z]+)([0-9]*)[@]((studenti)|(docente)|(segreteria))[.](unimi)[.](it)$')
);

create table if not exists Laurea(
    id varchar(10) primary key,
    nome_laurea varchar(100)
);

create table if not exists Corso(
    nome_corso varchar(100) primary key,
    anni smallint not null check( anni = 2 or anni = 3 or anni=5 or anni =6),
    laurea varchar(10) references Laurea(id) on update cascade on delete cascade not null
);

create table if not exists Studenti(
    matricola smallint primary key,
    email varchar(120) unique not null,
    anno_iscrizione date not null default make_date(extract(year from current_date)::int,09,01),
    frequenta varchar(100) default null references Corso(nome_corso),
    constraint anno_iscrizione_check check (1+(extract(year from current_date) - extract(year from anno_iscrizione))>= 1),
    foreign key (email) references Utenti(email) on update cascade on delete cascade
);

create table if not exists Insegnamento(
    id varchar(10) primary key,
    nome_insegnamento varchar(100) not null,
    descrizione text,
    anno smallint not null,
    semestre smallint not null,
    cfu smallint not null,
    corso varchar(100) references Corso(nome_corso),
    constraint anno_insegnamento_check check(anno >=0 and anno <=6),
    constraint semestre_insegnamento_check check(semestre >=0 and semestre <=2),
    constraint cfu_check check ( cfu >=1 and cfu <=60 )
);

create table if not exists Responsabile(
    docente varchar(120) references Utenti(email) on update cascade on delete cascade not null,
    insegnamento varchar(10) references Insegnamento(id) on update cascade on delete cascade not null,
    ruolo smallint default 1,
    constraint ruolo_check check (ruolo in (1,2)),
    primary key (docente,insegnamento)
);

create table if not exists Propedeutico(
    insegnamento varchar(10) references Insegnamento(id) on update cascade on delete cascade not null,
    propedeutico varchar(10) references Insegnamento(id) on update cascade on delete cascade not null,
    CONSTRAINT propedeutico_check check ( propedeutico <> insegnamento ),
    primary key(insegnamento,propedeutico)
);

create table if not exists Esame(
    docente varchar(120) references Utenti(email) on update cascade on delete set default not null default 'Docente cancellato',
    data_ora timestamp not null,
    insegnamento varchar(10) references Insegnamento(id) on update cascade on delete cascade,
    lettere varchar(3) not null default 'A-Z',
    constraint lettere_check check (lettere ~ '^[A-Z]-[A-Z]$'),
    primary key (insegnamento,data_ora,lettere)
);

create table if not exists Esami_studenti(
    studente smallint references Studenti(matricola) on update cascade on delete cascade not null,
    esame_insegnamento varchar(10),
    esame_lettere varchar(3),
    esame_data_ora timestamp,
    voto smallint,
    lode bool default false,
    accettato bool,
    constraint voto_check check ( voto >0 and voto <=30 or voto is null ),
    primary key (studente,esame_data_ora,esame_insegnamento),
    foreign key (esame_insegnamento,esame_data_ora,esame_lettere) references Esame(insegnamento,data_ora,lettere) on update cascade on delete cascade
);

create table if not exists Utenti_old(
   email varchar(120) primary key,
    nome varchar(50) not null,
    cognome varchar(50) not null,
    cf varchar(16) not null,
    tipologia varchar(20) not null,
    constraint codice_fiscale_ER check ( cf ~ '^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$' ),
    constraint tipologia_check check (tipologia in ('studente','docente','segretario')),
    constraint email_check check ( email ~ '^([a-z]+)[.]([a-z]+)([0-9]*)[@]((studenti)|(docente)|(segreteria))[.](unimi)[.](it)$')
);

create table if not exists Studenti_old(
    matricola smallint primary key,
    email varchar(120) not null unique,
    anno_iscrizione date not null default make_date(extract(year from current_date)::int,09,01),
    frequenta varchar(100) default null references Corso(nome_corso),
    constraint anno_iscrizione_check check (1+(extract(year from current_date) - extract(year from anno_iscrizione))>= 1),
    foreign key (email) references Utenti_old(email) on update cascade on delete cascade
);

create table if not exists Esami_studenti_old(
    studente smallint references Studenti_old(matricola) on update cascade on delete cascade not null,
    esame_insegnamento varchar(10),
    esame_lettere varchar(3),
    esame_data_ora timestamp,
    voto smallint,
    lode bool default false,
    accettato bool,
    constraint voto_check check ( voto >0 and voto <=30 or voto is null ),
    primary key (studente,esame_data_ora,esame_insegnamento),
    foreign key (esame_insegnamento,esame_data_ora,esame_lettere) references Esame(insegnamento,data_ora,lettere) on update cascade on delete cascade
);

create or replace function crea_email(nome varchar(50),cognome varchar(50),tipo varchar(20)) returns varchar(120) as $crea_email$
    declare
        e_mail varchar(120);
        nome_primo varchar(50);
        cognome_primo varchar(50);
        contatore int = (   with a as( select count(*) as count from utenti
                                    where utenti.cognome = $2 and utenti.nome=$1 and tipologia=tipo
                                    union
                                    select count(*) as count from utenti_old
                                    where utenti_old.cognome = $2 and utenti_old.nome=$1 and tipologia=tipo)
                            select sum(count) from a);
    begin
        if tipo not in ('studente','segretario','docente') then
                RAISE exception 'tipologia non esistente';
        end if;
        -- Prelevo unicamente il primo nome dell'utente (se ne possiede diversi separati da spazi) per generare l'email
        if nome not like ('% %') then
            nome_primo=nome;
        else
            select substring(nome from 1 for position(' ' in nome)-1) into nome_primo;
        end if;
        if cognome not like '% %' then
            cognome_primo=cognome;
        else
            select substring(cognome from 1 for position(' ' in cognome)-1) into cognome_primo;
        end if;

        if contatore = 0 then
            e_mail=lower(nome_primo)||'.'||lower(cognome_primo);
            else
                e_mail=lower(nome_primo)||'.'||lower(cognome_primo)||contatore;
        end if;
        case
            when tipo='segretario' then
                e_mail=e_mail||'@segreteria.unimi.it';
            when tipo='docente' then
                e_mail=e_mail||'@docente.unimi.it';
            when tipo='studente' then
                e_mail=e_mail||'@studenti.unimi.it';
            end case;
        return e_mail;
    end;
$crea_email$ language plpgsql;

create or replace function crea_user_before() returns trigger security definer set search_path = public as $$
    declare
        utente_old utenti%rowtype;
    begin
        if new.tipologia not in ('studente','docente','segretario') then
            raise exception 'errore tipologia';
        end if;
        if new.password is null then
            new.password=genera_password_random();
            raise notice '%',new.password;
        end if;
        select * into utente_old from utenti_old where cf = new.cf and tipologia=new.tipologia;
        if utente_old.email is not null then
            new.email=utente_old.email;
            new.nome=utente_old.nome;
            new.cognome=utente_old.cognome;
            raise notice 'Ripristino';
        else
            new.email =  crea_email(new.nome,new.cognome,new.tipologia);
        end if;
        execute 'create user '||quote_ident(new.email)||' with login encrypted password '||quote_literal(new.password);
        new.password=md5(new.password);
        return new;
    end
$$ language plpgsql;

create or replace function crea_user_after() returns trigger security definer set search_path = public as $$
    declare
        matricola_func int = (  with a as ( select max(matricola) as max
                                            from studenti
                                            union
                                            select max(matricola) as max
                                            from studenti_old)
                                select max(max) from a);
    begin
        if matricola_func is null then
            matricola_func = 0;
        end if;
        if exists(select email from utenti_old where email=new.email) then
            if (select tipologia from utenti_old where email=new.email) = 'studente' then
                insert into Studenti (email, matricola, anno_iscrizione)
                select email,matricola,current_date
                from Studenti_old
                where email=new.email;

                execute 'set session_replication_role = replica';
                insert into Esami_studenti(studente, esame_insegnamento, esame_lettere, esame_data_ora, voto, accettato)
                select studente, esame_insegnamento, esame_lettere, esame_data_ora, voto, accettato
                from Esami_studenti_old inner join Studenti_old on Esami_studenti_old.studente = Studenti_old.matricola
                where email = new.email;
                execute 'set session_replication_role = default';
            end if;

            delete from Utenti_old
            where email=new.email;
        else
            if new.tipologia='studente' then
            insert into Studenti(email, matricola)
                values (new.email,matricola_func+1);
            end if;
        end if;
        case
            when new.tipologia ='segretario' then
                execute 'grant segretario to '|| quote_ident(new.email);
            when new.tipologia ='studente' then
                execute 'grant studente to '|| quote_ident(new.email);
            when new.tipologia='docente' then
            execute 'grant docente to '|| quote_ident(new.email);
        end case;
        return new;
    end
$$ language plpgsql;

create or replace trigger insert_utente_before
before insert on Utenti
for each row execute function crea_user_before();

create or replace trigger insert_utente_after
after insert on Utenti
for each row execute function crea_user_after();

create type carriera_type as( nome_insegnamento varchar(100),data_ora date, voto smallint, lode bool,accettato bool, cfu smallint);

create unique index if not exists email_index on Utenti(email);
create unique index if not exists email_index_old on Utenti_old(email);
create unique index if not exists matricola_new on Studenti(matricola);
create unique index if not exists matricola_old on Studenti_old(matricola);
create unique index if not exists insegnamento_id on Insegnamento(id);

insert into laurea values
('L-31','SCIENZE E TECNOLOGIE INFORMATICHE'),
('LM-18','INFORMATICA'),
('L-27','SCIENZE E TECNOLOGIE CHIMICHE');

insert into corso values
('Informatica',3,'L-31'),
('Chimica',3,'L-27'),
('Informatica - magistrale',2,'LM-18');

-- I codici fiscali sono finti, non possono funzionare. E' unicamente giusta la loro struttura dei caratteri tranne.
insert into Utenti(nome, cognome, password, cf, tipologia) values
('Giacomo','Lucca','1234','AJFRFA62M82C675S','studente'),
('Giacomo','Lucca','1234','AJFRFT62M82C675S','studente'),
('Giacomo','Lucca','1234','AJQRFT62M82C675S','studente'),
('Giacomo','Lucca','1234','AJQRFT72M82C678S','studente'),
('Giacomo','Lucca','1234','AJQRFT72M82C675S','studente'),
('Giacomo','Lucca','24122002','AJQRYT62M82C675S','studente'),
('Enrico','Verdi','1234','LRHOVO65H24N889J','studente'),
('Mario','Rossi','12345','RSSMRA80A01H501U','segretario'),
('Giovanni','Pighizzini','1234','PLRFNU18E67O144F','docente'),
('Violetta','Lonati','1234','TCWKUN54A30C353H','docente'),
('Nunzio Alberto','Borghese','1234','RIGAYF14I40Q218H','docente'),
('Nicola','Basilico','1234','AGGHNC33W22T983P','docente'),
('Massimo Walter','Rivolta','1234','FTSLBB17T56M851P','docente'),
('Gabriella','Trucco','1234','UZXLKE67Q09W334Z','docente'),
('Stefano','Montanelli','1234','CFQLGI55L32G649Q','docente'),
('Valerio','Bellandi','1234','DBQECQ82D46O455I','docente'),
('Giovanni','Livraga','1234','CLYCQB24O88J776X','docente'),
('Elvinia Maria','Riccobene','1234','UDIZDE31B84Q335T','docente'),
('Beatrice Santa','Palano','1234','KVJDHW63C41D370O','docente'),
('Stefano','Aguzzoli','1234','JYOBYI80X84S623K','docente'),
('Cecilia','Cavaterra','1234','VNWCJR32L94T479T','docente'),
('Anna','Gori','1234','HFGFBQ75K34C582D','docente'),
('Alice','Garbagnati','1234','NEMYDK22X06Z148H','docente'),
('Sebastiano','Vigna','1234','VEAHBB06W64S168D','docente'),
('Paolo','Boldi','1234','FDPQNJ33C51P221C','docente'),
('Alberto','Ceselli','1234','BFLTLX63T82Z235Q','docente'),
('Massimo','Santini','1234','KRQKHI97S38E118X','docente'),
('Gian Paolo','Rossi','1234','URJUJL11C43A287K','docente'),
('Vincenzo','Piuri','1234','AAXPUR82Z59I298O','docente'),
('Dario','Malchiodi','1234','PYXLBW47C16Y393F','docente'),
('Nicolo Antonio','Cesa Bianchi','1234','TKLBUZ75O45V021E','docente'),
('Andrea Mario','Trentini','1234','QGIIAG35B44T261L','docente'),
('Matteo','Re','1234','PRBNDU09P55P433B','docente'),
('Marco','Tarini','1234','JXGDCE04Z03T595L','docente'),
('Andrea','Visconti','1234','XTMDLI19H32N394Y','docente'),
('Paolo','Ceravolo','1234','DUGIXS55E20M699D','docente'),
('Federico','Pedersini','1234','OKBXKT29W12A826Q','docente'),
('Raffaella','Lanzarotti','1234','BVOPUV92D48V794E','docente'),
('Giuliano','Grossi','1234','KNNKKC72M86S909R','docente'),
('Marco','Cosentino Lagomarsino','1234','JIPIUD60D22O329P','docente'),
('Matteo','Zingani','1234','FIQMPL42B66O423H','docente'),
('Chiara','Braghin','1234','HMMHDB08H29X859R','docente'),
('Walter','Cazzola','1234','KUNYXE76S08W124D','docente'),
('Camillo','Fiorentini','1234','CENFQZ98T86T220J','docente'),
('Marina','Bertolini','1234','JDULSX13V64G922O','docente'),
('Anna Chiara Giovanna','Morpurgo','1234','DAIPHZ31K74W070R','docente'),
('Lorenzo','Capra','1234','XOKPRL45J05B916T','docente'),
('Alessandro','D amelio','1234','KCSEBF90X46X843S','docente'),
('Nicola','Bianchessi','1234','FFHYKI92Q08I311K','docente'),
('Alberto Davide Adolfo','Momigliano','1234','XUBKDC02V15H259D','docente'),
('Elena','Pagani','1234','TUKXOY85R66Q628C','docente'),
('Giovanni','Righini','1234','FGYIJU40Z91V974V','docente'),
('Danilo Mauro','Bruschi','1234','CSWITO90B58N520A','docente'),
('Silvana','Castano','1234','EDJIAE04O12W330L','docente'),
('Ruggero Donida','Labati','1234','BXOMAC55V92D802I','docente'),
('Angelo','Genovese','1234','AKJALJ05X89U525O','docente'),
--('','','1234','','docente'),
('Anna','Gori','1234','LZMGLV98K57W315E','docente'),
('Nello','Scarabottolo','1234','NVAPIX77H07K417L','docente');

update studenti
set frequenta='Informatica'
where email like '%';

insert into insegnamento(id, nome_insegnamento,cfu, anno, semestre, corso, descrizione) values
('F1X-52','Algoritmi e strutture dati','12','2','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-55','Architettura degli elaboratori I','6','1','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-77','Architettura degli elaboratori II','6','2','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-94','Complementi di algoritmi e strutture dati','6','2','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-95','Aspetti etici legali sociali ed economici dell’informatica','3','3','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-51','Basi di dati','12','2','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-99','Ingegneria del software','12','3','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-67','Intelligenza Artificiale I','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-115','Logica matematica','6','1','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-59','Matematica del continuo','12','1','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-88','Matematica del discreto','6','1','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-56','Programmazione 1','12','1','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-125','Programmazione 2','6','2','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-50','Reti di Calcolatori','12','3','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-98','Sistemi Operativi','12','2','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-97','Statistica e analisi dei dati','6','2','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-89','Programmazione web e mobile','6','0','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-71','Crittografia 1','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-114','Editoria digitale','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F3X-29','Elaborazione dei segnali','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-81','Informazioni multimediale','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-57','Fisica','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F9X-35','Fondamenti di Social Media Digitali','6','0','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-54','Linguaggi di programmazione','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-90','Linguaggi e traduttori','6','0','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-43','Ricerca Operativa','6','0','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-78','Sicurezza e privatezza','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-122','Sistemi embedded','6','0','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-63','Sistemi informativi','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-128','Programmazione dichiarativa','6','0','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
('F1X-109','Visualizzazione scientifica','6','0','1','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.'),
--('','','','','','',''),
('F1X-116','Linguaggi formali e automi','6','1','2','Informatica','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec rutrum vitae nisi sit amet lobortis. Aliquam et mauris vestibulum metus porttitor finibus. Morbi at tincidunt purus, a suscipit ipsum. Morbi odio ipsum, ullamcorper non eros eu, iaculis posuere neque.');

insert into responsabile(docente, insegnamento, ruolo) values
('giovanni.pighizzini@docente.unimi.it','F1X-52',1),
('violetta.lonati@docente.unimi.it','F1X-52',2),
('nunzio.borghese@docente.unimi.it','F1X-55',1),
('nicola.basilico@docente.unimi.it','F1X-55',1),
('massimo.rivolta@docente.unimi.it','F1X-55',2),
('gabriella.trucco@docente.unimi.it','F1X-55',2),
('matteo.re@docente.unimi.it','F1X-55',2),
('nunzio.borghese@docente.unimi.it','F1X-77',1),
('nicola.basilico@docente.unimi.it','F1X-77',1),
('massimo.rivolta@docente.unimi.it','F1X-77',2),
('matteo.re@docente.unimi.it','F1X-77',2),
('andrea.trentini@docente.unimi.it','F1X-95',1),
('nello.scarabottolo@docente.unimi.it','F1X-95',2),
('nicolo.cesa@docente.unimi.it','F1X-95',2),
('stefano.montanelli@docente.unimi.it','F1X-51',1),
('valerio.bellandi@docente.unimi.it','F1X-51',2),
('giovanni.livraga@docente.unimi.it','F1X-51',2),
('nicolo.cesa@docente.unimi.it','F1X-94',1),
('andrea.visconti@docente.unimi.it','F1X-71',1),
('paolo.ceravolo@docente.unimi.it','F1X-114',1),
('federico.pedersini@docente.unimi.it','F3X-29',1),
('giuliano.grossi@docente.unimi.it','F1X-81',1),
('raffaella.lanzarotti@docente.unimi.it','F1X-81',2),
('marco.cosentino@docente.unimi.it','F1X-57',1),
('danilo.bruschi@docente.unimi.it','F9X-35',1),
('matteo.zingani@docente.unimi.it','F9X-35',2),
('elvinia.riccobene@docente.unimi.it','F1X-99',1),
('chiara.braghin@docente.unimi.it','F1X-99',2),
('nicola.basilico@docente.unimi.it','F1X-67',1),
('walter.cazzola@docente.unimi.it','F1X-54',1),
('massimo.santini@docente.unimi.it','F1X-90',1),
('beatrice.palano@docente.unimi.it','F1X-116',1),
('stefano.aguzzoli@docente.unimi.it','F1X-115',1),
('camillo.fiorentini@docente.unimi.it','F1X-115',2),
('cecilia.cavaterra@docente.unimi.it','F1X-59',1),
('anna.gori@docente.unimi.it','F1X-59',2),
('alice.garbagnati@docente.unimi.it','F1X-88',1),
('marina.bertolini@docente.unimi.it','F1X-88',2),
('sebastiano.vigna@docente.unimi.it','F1X-56',1),
('paolo.boldi@docente.unimi.it','F1X-56',1),
('alberto.ceselli@docente.unimi.it','F1X-56',1),
('andrea.trentini@docente.unimi.it','F1X-56',2),
('anna.morpurgo@docente.unimi.it','F1X-56',2),
('lorenzo.capra@docente.unimi.it','F1X-56',2),
('alessandro.d@docente.unimi.it','F1X-56',2),
('nicola.bianchessi@docente.unimi.it','F1X-56',2),
('camillo.fiorentini@docente.unimi.it','F1X-128',1),
('alberto.momigliano@docente.unimi.it','F1X-128',2),
('massimo.santini@docente.unimi.it','F1X-125',1),
('gian.rossi@docente.unimi.it','F1X-50',1),
('elena.pagani@docente.unimi.it','F1X-50',2),
('giovanni.righini@docente.unimi.it','F1X-43',1),
('danilo.bruschi@docente.unimi.it','F1X-78',1),
('andrea.trentini@docente.unimi.it','F1X-122',1),
('silvana.castano@docente.unimi.it','F1X-63',1),
('vincenzo.piuri@docente.unimi.it','F1X-98',1),
('ruggero.labati@docente.unimi.it','F1X-98',2),
('angelo.genovese@docente.unimi.it','F1X-98',2),
('dario.malchiodi@docente.unimi.it','F1X-97',1),
('valerio.bellandi@docente.unimi.it','F1X-89',1),
--('','',''),
('elena.pagani@docente.unimi.it','F1X-109',1);

insert into propedeutico(propedeutico, insegnamento) values
('F1X-52','F1X-56'),
('F1X-51','F1X-56'),
('F1X-54','F1X-56'),
('F1X-125','F1X-56'),
('F1X-98','F1X-56'),
('F1X-43','F1X-88'),
--('',''),
('F1X-97','F1X-59');

insert into esame(docente, data_ora, insegnamento,lettere) values
('alberto.ceselli@docente.unimi.it','2022-09-01 08:30:00','F1X-56','A-I'),
('paolo.boldi@docente.unimi.it','2022-09-01 08:30:00','F1X-56','I-Q'),
('sebastiano.vigna@docente.unimi.it','2022-09-01 08:30:00','F1X-56','Q-Z'),
('giovanni.pighizzini@docente.unimi.it','2022-09-02 14:30:00','F1X-52','A-Z'),
('nunzio.borghese@docente.unimi.it','2022-09-03 09:30:00','F1X-77','N-Z'),
('nicola.basilico@docente.unimi.it','2022-09-03 09:30:00','F1X-77','A-N'),
('andrea.trentini@docente.unimi.it','2022-09-04 00:00:00','F1X-95','A-Z'),
('stefano.montanelli@docente.unimi.it','2022-09-05 14:30:00','F1X-51','A-Z'),
('dario.malchiodi@docente.unimi.it','2022-09-06 08:30:00','F1X-97','A-Z'),
('nicolo.cesa@docente.unimi.it','2022-09-07 00:00:00','F1X-94','A-Z'),
('walter.cazzola@docente.unimi.it','2022-09-08 00:00:00','F1X-54','A-Z'),
('andrea.visconti@docente.unimi.it','2022-09-09 09:30:00','F1X-71','A-Z'),
('paolo.ceravolo@docente.unimi.it','2022-09-10 14:30:00','F1X-114','A-Z'),
('giovanni.righini@docente.unimi.it','2022-09-11 00:00:00','F1X-43','A-Z'),
('giuliano.grossi@docente.unimi.it','2022-09-12 09:30:00','F1X-81','A-Z'),
('danilo.bruschi@docente.unimi.it','2022-09-13 08:30:00','F9X-35','A-Z'),
('elvinia.riccobene@docente.unimi.it','2022-09-14 00:00:00','F1X-99','A-Z'),
('massimo.santini@docente.unimi.it','2022-09-15 00:00:00','F1X-90','A-Z'),
('nicola.basilico@docente.unimi.it','2022-09-16 15:30:00','F1X-67','A-Z'),
('beatrice.palano@docente.unimi.it','2022-09-17 13:30:00','F1X-116','A-Z'),
('stefano.aguzzoli@docente.unimi.it','2022-09-18 00:00:00','F1X-115','A-Z'),
('cecilia.cavaterra@docente.unimi.it','2022-09-19 12:00:00','F1X-59','A-Z'),
('alice.garbagnati@docente.unimi.it','2022-09-20 00:00:00','F1X-88','A-Z'),
('camillo.fiorentini@docente.unimi.it','2022-09-21 15:00:00','F1X-128','A-Z'),
('massimo.santini@docente.unimi.it','2022-09-22 00:00:00','F1X-125','A-Z'),
('gian.rossi@docente.unimi.it','2022-09-23 00:00:00','F1X-50','A-Z'),
('danilo.bruschi@docente.unimi.it','2022-09-24 00:00:00','F1X-78','A-Z'),
('andrea.trentini@docente.unimi.it','2022-09-25 00:00:00','F1X-122','A-Z'),
('silvana.castano@docente.unimi.it','2022-09-26 00:00:00','F1X-63','A-Z'),
('vincenzo.piuri@docente.unimi.it','2022-09-27 00:00:00','F1X-98','A-Z'),
('valerio.bellandi@docente.unimi.it','2022-09-28 00:00:00','F1X-89','A-Z'),
('elena.pagani@docente.unimi.it','2022-09-29 00:00:00','F1X-109','A-Z'),
('nunzio.borghese@docente.unimi.it','2022-09-30 09:30:00','F1X-55','N-Z'),
('nicola.basilico@docente.unimi.it','2022-09-30 09:30:00','F1X-55','A-N'),
('federico.pedersini@docente.unimi.it','2022-10-01 00:00:00','F3X-29','A-Z'),
('marco.cosentino@docente.unimi.it','2022-10-02 00:00:00','F1X-57','A-Z'),
('alberto.ceselli@docente.unimi.it','2023-09-01 08:30:00','F1X-56','A-I'),
('paolo.boldi@docente.unimi.it','2023-09-01 08:30:00','F1X-56','I-Q'),
('sebastiano.vigna@docente.unimi.it','2023-09-01 08:30:00','F1X-56','Q-Z'),
('giovanni.pighizzini@docente.unimi.it','2023-09-02 14:30:00','F1X-52','A-Z'),
('nunzio.borghese@docente.unimi.it','2023-09-03 09:30:00','F1X-77','N-Z'),
('nicola.basilico@docente.unimi.it','2023-09-03 09:30:00','F1X-77','A-N'),
('andrea.trentini@docente.unimi.it','2023-09-04 00:00:00','F1X-95','A-Z'),
('stefano.montanelli@docente.unimi.it','2023-09-05 14:30:00','F1X-51','A-Z'),
('dario.malchiodi@docente.unimi.it','2023-09-06 08:30:00','F1X-97','A-Z'),
('nicolo.cesa@docente.unimi.it','2023-09-07 00:00:00','F1X-94','A-Z'),
('walter.cazzola@docente.unimi.it','2023-09-08 00:00:00','F1X-54','A-Z'),
('andrea.visconti@docente.unimi.it','2023-09-09 09:30:00','F1X-71','A-Z'),
('paolo.ceravolo@docente.unimi.it','2023-09-10 14:30:00','F1X-114','A-Z'),
('giovanni.righini@docente.unimi.it','2023-09-11 00:00:00','F1X-43','A-Z'),
('giuliano.grossi@docente.unimi.it','2023-09-12 09:30:00','F1X-81','A-Z'),
('danilo.bruschi@docente.unimi.it','2023-09-13 08:30:00','F9X-35','A-Z'),
('elvinia.riccobene@docente.unimi.it','2023-09-14 00:00:00','F1X-99','A-Z'),
('massimo.santini@docente.unimi.it','2023-09-15 00:00:00','F1X-90','A-Z'),
('nicola.basilico@docente.unimi.it','2023-09-16 15:30:00','F1X-67','A-Z'),
('beatrice.palano@docente.unimi.it','2023-09-17 13:30:00','F1X-116','A-Z'),
('stefano.aguzzoli@docente.unimi.it','2023-09-18 00:00:00','F1X-115','A-Z'),
('cecilia.cavaterra@docente.unimi.it','2023-09-19 12:00:00','F1X-59','A-Z'),
('alice.garbagnati@docente.unimi.it','2023-09-20 00:00:00','F1X-88','A-Z'),
('camillo.fiorentini@docente.unimi.it','2023-09-21 15:00:00','F1X-128','A-Z'),
('massimo.santini@docente.unimi.it','2023-09-22 00:00:00','F1X-125','A-Z'),
('gian.rossi@docente.unimi.it','2023-09-23 00:00:00','F1X-50','A-Z'),
('danilo.bruschi@docente.unimi.it','2023-09-24 00:00:00','F1X-78','A-Z'),
('andrea.trentini@docente.unimi.it','2023-09-25 00:00:00','F1X-122','A-Z'),
('silvana.castano@docente.unimi.it','2023-09-26 00:00:00','F1X-63','A-Z'),
('vincenzo.piuri@docente.unimi.it','2023-09-27 00:00:00','F1X-98','A-Z'),
('valerio.bellandi@docente.unimi.it','2023-09-28 00:00:00','F1X-89','A-Z'),
('elena.pagani@docente.unimi.it','2023-09-29 00:00:00','F1X-109','A-Z'),
('nunzio.borghese@docente.unimi.it','2023-09-30 09:30:00','F1X-55','N-Z'),
('nicola.basilico@docente.unimi.it','2023-09-30 09:30:00','F1X-55','A-N'),
('federico.pedersini@docente.unimi.it','2023-10-01 00:00:00','F3X-29','A-Z'),
('marco.cosentino@docente.unimi.it','2023-10-02 00:00:00','F1X-57','A-Z'),
('alberto.ceselli@docente.unimi.it','2021-09-01 08:30:00','F1X-56','A-I'),
('paolo.boldi@docente.unimi.it','2021-09-01 08:30:00','F1X-56','I-Q'),
('sebastiano.vigna@docente.unimi.it','2021-09-01 08:30:00','F1X-56','Q-Z'),
('giovanni.pighizzini@docente.unimi.it','2021-09-02 14:30:00','F1X-52','A-Z'),
('nunzio.borghese@docente.unimi.it','2021-09-03 09:30:00','F1X-77','N-Z'),
('nicola.basilico@docente.unimi.it','2021-09-03 09:30:00','F1X-77','A-N'),
('andrea.trentini@docente.unimi.it','2021-09-04 00:00:00','F1X-95','A-Z'),
('stefano.montanelli@docente.unimi.it','2021-09-05 14:30:00','F1X-51','A-Z'),
('dario.malchiodi@docente.unimi.it','2021-09-06 08:30:00','F1X-97','A-Z'),
('nicolo.cesa@docente.unimi.it','2021-09-07 00:00:00','F1X-94','A-Z'),
('walter.cazzola@docente.unimi.it','2021-09-08 00:00:00','F1X-54','A-Z'),
('andrea.visconti@docente.unimi.it','2021-09-09 09:30:00','F1X-71','A-Z'),
('paolo.ceravolo@docente.unimi.it','2021-09-10 14:30:00','F1X-114','A-Z'),
('giovanni.righini@docente.unimi.it','2021-09-11 00:00:00','F1X-43','A-Z'),
('giuliano.grossi@docente.unimi.it','2021-09-12 09:30:00','F1X-81','A-Z'),
('danilo.bruschi@docente.unimi.it','2021-09-13 08:30:00','F9X-35','A-Z'),
('elvinia.riccobene@docente.unimi.it','2021-09-14 00:00:00','F1X-99','A-Z'),
('massimo.santini@docente.unimi.it','2021-09-15 00:00:00','F1X-90','A-Z'),
('nicola.basilico@docente.unimi.it','2021-09-16 15:30:00','F1X-67','A-Z'),
('beatrice.palano@docente.unimi.it','2021-09-17 13:30:00','F1X-116','A-Z'),
('stefano.aguzzoli@docente.unimi.it','2021-09-18 00:00:00','F1X-115','A-Z'),
('cecilia.cavaterra@docente.unimi.it','2021-09-19 12:00:00','F1X-59','A-Z'),
('alice.garbagnati@docente.unimi.it','2021-09-20 00:00:00','F1X-88','A-Z'),
('camillo.fiorentini@docente.unimi.it','2021-09-21 15:00:00','F1X-128','A-Z'),
('massimo.santini@docente.unimi.it','2021-09-22 00:00:00','F1X-125','A-Z'),
('gian.rossi@docente.unimi.it','2021-09-23 00:00:00','F1X-50','A-Z'),
('danilo.bruschi@docente.unimi.it','2021-09-24 00:00:00','F1X-78','A-Z'),
('andrea.trentini@docente.unimi.it','2021-09-25 00:00:00','F1X-122','A-Z'),
('silvana.castano@docente.unimi.it','2021-09-26 00:00:00','F1X-63','A-Z'),
('vincenzo.piuri@docente.unimi.it','2021-09-27 00:00:00','F1X-98','A-Z'),
('valerio.bellandi@docente.unimi.it','2021-09-28 00:00:00','F1X-89','A-Z'),
('elena.pagani@docente.unimi.it','2021-09-29 00:00:00','F1X-109','A-Z'),
('nunzio.borghese@docente.unimi.it','2021-09-30 09:30:00','F1X-55','N-Z'),
('nicola.basilico@docente.unimi.it','2021-09-30 09:30:00','F1X-55','A-N'),
('federico.pedersini@docente.unimi.it','2021-10-01 00:00:00','F3X-29','A-Z'),
('marco.cosentino@docente.unimi.it','2021-10-02 00:00:00','F1X-57','A-Z');

insert into esami_studenti(studente, esame_insegnamento,esame_data_ora, voto, accettato, esame_lettere) values('2','F1X-56','2021-09-01 08:30:00','9','false','I-Q'),
('2','F1X-56','2022-09-01 08:30:00','29','true','I-Q'),
('2','F1X-52','2022-09-02 14:30:00','26','true','A-Z'),
('2','F1X-77','2022-09-03 09:30:00','28','true','A-N'),
('2','F1X-95','2022-09-04 00:00:00','25','true','A-Z'),
('2','F1X-51','2022-09-05 14:30:00','25','true','A-Z'),
('2','F1X-97','2022-09-06 08:30:00','25','true','A-Z'),
('2','F1X-94','2022-09-07 00:00:00','25','true','A-Z'),
('2','F1X-54','2022-09-08 00:00:00','25','true','A-Z'),
('2','F1X-71','2022-09-09 09:30:00','25','true','A-Z'),
('2','F1X-114','2022-09-10 14:30:00','25','true','A-Z'),
('2','F1X-43','2022-09-11 00:00:00','25','true','A-Z'),
('2','F1X-81','2022-09-12 09:30:00','25','true','A-Z'),
('2','F9X-35','2022-09-13 08:30:00','25','true','A-Z'),
('2','F1X-99','2022-09-14 00:00:00','25','true','A-Z'),
('2','F1X-90','2022-09-15 00:00:00','25','true','A-Z'),
('2','F1X-67','2022-09-16 15:30:00','25','true','A-Z'),
('2','F1X-116','2022-09-17 13:30:00','25','true','A-Z'),
('2','F1X-115','2022-09-18 00:00:00','25','true','A-Z'),
('2','F1X-59','2022-09-19 12:00:00','25','true','A-Z'),
('2','F1X-88','2022-09-20 00:00:00','25','true','A-Z'),
('2','F1X-128','2022-09-21 15:00:00','25','true','A-Z'),
('2','F1X-125','2022-09-22 00:00:00','25','true','A-Z'),
('2','F1X-50','2022-09-23 00:00:00','25','true','A-Z'),
('2','F1X-78','2022-09-24 00:00:00','25','true','A-Z'),
('2','F1X-122','2022-09-25 00:00:00','25','true','A-Z'),
('2','F1X-63','2022-09-26 00:00:00','25','true','A-Z'),
('2','F1X-98','2022-09-27 00:00:00','25','true','A-Z'),
('2','F1X-89','2022-09-28 00:00:00','25','true','A-Z'),
('2','F1X-109','2022-09-29 00:00:00','25','true','A-Z'),
('2','F1X-55','2022-09-30 09:30:00','25','true','A-N'),
('2','F3X-29','2022-10-01 00:00:00','25','true','A-Z'),
('2','F1X-57','2022-10-02 00:00:00','25','true','A-Z');

create or replace function check_lode() returns trigger as $$
    begin
        if new.voto <> 30 and new.lode then
            raise exception 'Impossibile inserire la lode';
        end if;
        return new;
    end;
$$ language plpgsql;

create or replace function inserisci_corso_before() returns trigger as $$
    begin
        if new.anno > (select anni from corso where nome_corso=new.corso) then
            raise exception 'Non puoi inserire un anno superiore al massimo degli anni del corso';
        end if;
        return new;
    end;
$$ language plpgsql;

create or replace function update_password() returns trigger security definer set search_path = public as $$
    begin
        if (select tipologia from utenti where email = current_user) in('docente','studente') then
            if new.email <> current_user then
                raise exception 'non puoi cambiare la password per utenti che non ti competono';
            end if;
        end if;
        execute 'alter user '||quote_ident(new.email)||' with encrypted password '||quote_literal(new.password);
        new.password=md5(new.password);
        return new;
    end;
$$ language plpgsql;

create or replace function genera_password_random() returns varchar(10) as $$
    declare
        i int = 0;
        password varchar(10) = '';
    begin
        while i < 10
        loop
            if random() ::int % 2 = 0 then
                password = password || chr((random()*26)::int + 96);
            else
                password = password || chr((random()*26)::int + 65);
            end if;
            i=i+1;
        end loop;
        return password;
    end;
$$ language plpgsql;

create or replace function sposta_utente_before() returns trigger security definer set search_path = public as $$
    begin

        insert into Utenti_old(email, nome, cognome, tipologia,cf)
        select email,nome,cognome,tipologia,cf
        from Utenti
        where email=old.email;

        raise notice '%',old;

        if (select tipologia from utenti where email=old.email) = 'studente' then

            insert into Studenti_old (email, matricola, anno_iscrizione,frequenta)
            select email,matricola,anno_iscrizione,frequenta
            from Studenti
            where email=old.email;

            insert into esami_studenti_old(studente, esame_insegnamento, esame_lettere, esame_data_ora, voto, accettato)
            select studente, esame_insegnamento, esame_lettere, esame_data_ora, voto, accettato
            from Esami_studenti inner join studenti on esami_studenti.studente=studenti.matricola
            where matricola=(select matricola from studenti where email=old.email);
        end if;

        execute 'drop user '||quote_ident(old.email);
        return old;
    end;
$$ language plpgsql;

create or replace function laurea(stud varchar(120)) returns bool as $$
    declare
        rimasti smallint;
        cfu_sum smallint;
        matr smallint = (select matricola from studenti where email=stud);
        course varchar(100) = (select frequenta from studenti where matricola=matr);
        anni smallint = (select anni from corso where nome_corso = course);
    begin
        with a as(select id
                  from insegnamento
                  where corso=course and anno in (1,2,3,4,5,6)),
        b as(
            select distinct (insegnamento) as id
            from esami_studenti inner join Esame as es on esami_studenti.esame_insegnamento = es.insegnamento and esami_studenti.esame_data_ora = es.data_ora and esami_studenti.esame_lettere = es.lettere
            where studente=matr and accettato =true and voto >= 18 and data_ora = (
                select max(data_ora)
                from esami_studenti inner join Esame on esami_studenti.esame_insegnamento = Esame.insegnamento and esami_studenti.esame_data_ora = Esame.data_ora and esami_studenti.esame_lettere = Esame.lettere
                                    inner join insegnamento on Esame.insegnamento = insegnamento.id
                where studente=matr and esame.insegnamento=es.insegnamento and anno in (1,2,3,4,5,6))),
        c as (
            select id from a except select id from b)

        select count(*) into rimasti from c;

        if rimasti = 0 then
            select sum(cfu) as id into cfu_sum
            from esami_studenti as es inner join Insegnamento I on I.id = es.esame_insegnamento
            where studente=matr and voto >= 18 and accettato=true and esame_data_ora = (
                select max(esame_data_ora)
                from esami_studenti inner join insegnamento on esame_insegnamento = insegnamento.id
                where studente=matr and es.esame_insegnamento=esame_insegnamento);
            if (anni = 3 and cfu_sum >=180) or (anni=2 and cfu_sum>=120) or (anni=5 and cfu_sum>=300) or (anni=6 and cfu_sum>=360) then
                return true;
            end if;
        end if;
        return false;
    end;
$$ language plpgsql;

create or replace function visualizza_carriera(studente varchar(120)) returns setof carriera_type as $$
    declare
        matr smallint = (select matricola from studenti where email=studente);
    begin
        if current_user = 'postgres' or (select tipologia from utenti where email=current_user) in( 'segreterio', 'studente') then
            return query(
                select nome_insegnamento,date(esame_data_ora),voto,lode,accettato,cfu
                from insegnamento inner join esami_studenti on id=esame_insegnamento
                where Esami_studenti.studente=matr and voto is not null
            );
        else
            raise exception 'Non puoi vedere la carriera di un altro studente';
        end if;
    end;
$$ language plpgsql;

--Funzione che permette di visualizzare la carriera valida di uno studente
create or replace function visualizza_carriera_valida(stud varchar(120)) returns setof carriera_type as $$
    begin
        if session_user = 'postgres' or session_user = stud or (select tipologia from utenti where email=session_user) = 'segreterio' then
            return query (  select *
                            from visualizza_carriera(stud) as v1
                            where data_ora = (  select max(data_ora)
                                                from visualizza_carriera(stud)
                                                where voto >= 18 and accettato=true and v1.nome_insegnamento=nome_insegnamento)
                );
        else
            raise exception 'Non puoi vedere la carriera valida di un altro studente';
        end if;
    end;
$$ language plpgsql;

create or replace function prof_responsabile() returns trigger as $$
    begin
        if new.ruolo=1 and (select count(*) from responsabile where docente=new.docente and ruolo = 1) >=3 then
            raise exception 'Massimo numero di insegnamento del docente raggiunto come responsabile';
        end if;
        return new;
    end;
$$ language plpgsql;

create or replace function responsabili_before() returns trigger as $$
    BEGIN
        if (select tipologia from utenti where email=new.docente) <> 'docente' then
            raise exception 'Non può essere responsabile un utente che non sia un docente';
        end if;
        if (select count(*) from responsabile where insegnamento=new.insegnamento and ruolo=1) = 0 and new.ruolo<>1 then
            raise exception 'Non esiste un docente responsabile';
        end if;
        return new;
    end;
$$ language plpgsql;

create or replace function controllo_propedeuticità_esame() returns trigger as $$
    -- controllo durante l'iscrizione di un esame
    declare
        rec record;
    begin
        for rec in (select esame.insegnamento from esame inner join propedeutico on propedeutico=esame.insegnamento where data_ora=new.esame_data_ora and lettere=new.esame_lettere and Esame.insegnamento=new.esame_insegnamento)
        loop
            if not exists(
                select *
                from esami_studenti inner join esame as es on esami_studenti.esame_insegnamento = es.insegnamento and esami_studenti.esame_data_ora = es.data_ora and esami_studenti.esame_lettere = es.lettere
                where es.insegnamento=rec.insegnamento and studente=new.studente and voto >= 18 and accettato=true and data_ora = (
                    select max(data_ora) from esami_studenti as esami_interno inner join esame on esami_interno.esame_insegnamento = esame.insegnamento and esami_interno.esame_data_ora = esame.data_ora and esami_interno.esame_lettere = esame.lettere
                    where esami_interno.studente=new.studente and esame.insegnamento=es.insegnamento)
            ) then
                raise exception 'Non puoi iscriverti, mancano degli esami';
            end if;
        end loop;
        return new;
    end;
$$ language plpgsql;

create or replace function controllo_propedeuticità() returns trigger as $$
    --con una BFS
    declare
        visitati varchar(100)[] :='{}';
        da_visitare varchar(100)[]='{}';
        i int = 1;
        vet varchar(100)[]='{}';
        nodo varchar(100);
        rec record;
    begin
        if (select count(*) from propedeutico where propedeutico=new.insegnamento and insegnamento=new.propedeutico) >=1 then
            raise exception 'Impossibile inserire la propedeuticità. Risulterà impossibile eseguirlo se si aggiunge';
        end if;
        visitati:= visitati || new.insegnamento;
        for rec in (select propedeutico as p from propedeutico where insegnamento=new.propedeutico)
        loop
            da_visitare:=da_visitare || rec.p::varchar(100);
        end loop;
        while true
        loop
            if (select count(*) from unnest(da_visitare)) = (i-1) then
                exit;
            end if;
            visitati:=visitati || da_visitare[i];
            for rec in (select insegnamento from propedeutico where propedeutico=da_visitare[i])
            loop
                vet:=vet || rec.insegnamento::varchar(100);
            end loop;
            foreach nodo in ARRAY vet
            loop
                for rec in (select insegnamento from propedeutico where propedeutico = nodo)
                loop
                     if rec.insegnamento = any (visitati) then
                        raise exception 'Impossibile inserire la propedeuticità. Risulterà impossibile eseguirlo se si aggiungesse';
                    end if;
                    da_visitare:=da_visitare || rec.insegnamento::varchar(100);
                end loop;
            end loop;
            i=i+1;
            vet='{}';
        end loop;
        return new;
    end;
$$ language plpgsql;

create or replace function iscrizione_esame() returns trigger as $$
    begin
        if current_date > date(new.esame_data_ora)-7 then
            raise exception 'Impossibile iscriversi a questo esame, è già avvenuto';
        end if;
        return new;
    end;
$$ language plpgsql;

create or replace function inserire_esame() returns trigger security definer set search_path = public as $$
    declare
        rec record;
        num int = (select count(*) from responsabile where insegnamento=new.insegnamento and ruolo = 1);
        ris_float int = (((26/num ::float) - (26/num::int))*num) ::int;
        ris_int int = 26/num;
        lettera_inizio char;
        lettera_fine char;
        count int = 65;
        i int = 1;
    begin
        if ((new.data_ora::date) - current_date) < 14 then
            raise exception 'Impossibile aggiungere il suddetto esame in quel giorno. Troppi pochi giorni di preavviso.';
        end if;
        if exists(select data_ora from esame inner join insegnamento on esame.insegnamento = insegnamento.id where data_ora::date = new.data_ora::date and anno =(select anno from insegnamento where id=new.insegnamento) and corso=(select corso from insegnamento where insegnamento=new.insegnamento)) then
            raise exception 'Impossibile aggiungere il suddetto esame in quel giorno. Giorno già occupato.';
        end if;
        if (select tipologia from utenti where email=new.docente)<>'docente' then
            raise exception 'Impossibile inserire esame. Utente non docente selezionato';
        end if;
        execute 'set session_replication_role = replica'; --disabilita i trigger in questa sessione
        for rec in (select docente from responsabile where insegnamento=new.insegnamento and ruolo=1 order by docente)
        loop
            if i > (num-1) then
                lettera_inizio = chr(count);
                count = count+ris_int+ris_float -1 ;
                lettera_fine=chr(count);
            else
                lettera_inizio = chr(count);
                count = count+ris_int;
                lettera_fine=chr(count);
            end if;
            if rec.docente = new.docente then
                new.lettere=lettera_inizio||'-'||lettera_fine;
            else
                insert into esame(docente, data_ora, insegnamento,lettere) values
                (rec.docente,new.data_ora,new.insegnamento,lettera_inizio||'-'||lettera_fine);
            end if;
            i=i+1;
        end loop;
        execute 'set session_replication_role = default'; --riabilita i trigger in questa sessione
        return new;
    end;
$$ language plpgsql;

create or replace function update_esame() returns trigger security definer set search_path = public as $$
    declare
        role varchar(20) = (select tipologia from utenti where email = current_user);
    begin
        if role = 'docente' then
            if new.docente is null then
                new.docente=current_user;
            else if new.docente <> all(select docente from responsabile where insegnamento=new.insegnamento and ruolo=1) then
                    raise exception 'Errore, docente non è uguale allo user attuale';
                end if;
            end if;
        else
            if new.docente is null then
                raise exception 'Docente is null';
            end if;
        end if;
        if (new.data_ora::date - current_date < 14) then
            raise exception 'Errore 3';
        end if;
        if (new.data_ora)::date <> (select data_ora from esame where insegnamento=new.insegnamento and data_ora=old.data_ora limit 1)::date then
            if exists(select data_ora from esame where data_ora::date = new.data_ora::date and insegnamento <> new.insegnamento) then
                raise exception 'Impossibile aggiungere il suddetto esame in quel giorno. Giorno già occupato';
            end if;
            execute 'set session_replication_role = replica'; --disabilita i trigger in questa sessione
            update esame
            set data_ora=new.data_ora
            where insegnamento=new.insegnamento and data_ora=old.data_ora and docente<>new.docente;
            execute 'set session_replication_role = default'; --disabilita i trigger in questa sessione
        end if;
        return new;
    end;
$$ language  plpgsql;

create or replace function cancella_esame() returns trigger security definer set search_path = public as $$
    declare
        role varchar(20) = (select tipologia from utenti where email = current_user);
    begin
        if role = 'docente' then
            if old.docente is null then
                old.docente=current_user;
            else if old.docente <> all(select docente from responsabile where insegnamento=old.insegnamento and ruolo=1) then
                    raise exception 'Errore, docente non è uguale allo user attuale';
                end if;
            end if;
        else
            if old.docente is null then
                raise exception 'Docente is null';
            end if;
        end if;
        if new.data_ora::date - current_date < 7 then
            raise exception 'Errore 3';
        end if;

        execute 'set session_replication_role = replica'; --disabilita i trigger in questa sessione
        delete from esame where insegnamento=old.insegnamento and data_ora=old.data_ora and docente<>old.docente;
        execute 'set session_replication_role = default'; --disabilita i trigger in questa sessione
        return old;
    end;
$$ language plpgsql;

create or replace function controllo_lettera_esame() returns trigger as $$
    declare
        carattere char = upper(left((select cognome from utenti inner join studenti on utenti.email = studenti.email where matricola=new.studente),1));
        range varchar(3) = (select lettere from esame where insegnamento=new.esame_insegnamento and insegnamento=new.esame_insegnamento and data_ora=new.esame_data_ora and lettere=new.esame_lettere);
    begin
        if not(carattere > left(range,1) and carattere < "right"(range,1)) then
           raise exception 'Non puoi sostenere questo esame con questo docente';
        end if;
        return new;
    end;
$$ language plpgsql;

--Funzione che trova una corrispondenza tra l'email e la password per effetturare il login nel database
create or replace function esiste_utente(email varchar(120), psw text) returns setof varchar(20) security definer set search_path = public as $$
    begin
        return query(select tipologia
                from Utenti
                where password=md5($2) and utenti.email=$1);
    end;
$$ language plpgsql;

create or replace function elimina_esame_before() returns trigger as $$
    begin
        if ((select tipologia from studenti inner join utenti on studenti.email = utenti.email where studenti.matricola=old.studente)='studente' and (select email from studenti where matricola=old.studente)<>current_user)and(current_date+7<old.esame_data_ora) then
            raise exception 'Non puoi cancellare codesto esame';
        end if;
        return old;
    end;
$$ language plpgsql;

create or replace function insufficiente() returns trigger as $$
    begin
        if new.voto <> null and new.voto <18 then
            new.accettato=false;
        end if;
        return new;
    end;
$$ language plpgsql;

create or replace trigger insufficiente
before update on esami_studenti
for each row execute function insufficiente();

create or replace trigger elimina_esame_before
before delete on Esami_studenti
for each row execute function elimina_esame_before();

create or replace trigger check_lode
before insert on Esami_studenti
for each row execute function check_lode();

create or replace trigger controllo_propedeuticità
after insert on propedeutico
for each row execute function controllo_propedeuticità();

create or replace trigger responsabili_before
before insert on Responsabile
for each row execute function responsabili_before();

create or replace trigger prof_responsabile_before
before insert on Responsabile
for each row execute function prof_responsabile();

create or replace trigger sposta_utente
before delete on Utenti
for each row execute function sposta_utente_before();

create or replace trigger cambia_password
before update on Utenti
for each row
when (new.password is not null)
execute function update_password();

create or replace trigger inserisci_corso_before
before insert on insegnamento
execute function inserisci_corso_before();

create or replace trigger controllo_lettera_esame
before insert on Esami_studenti
for each row execute function controllo_lettera_esame();

create or replace trigger controllo_propedeuticità_esame_before
before insert on Esami_studenti
for each row execute function controllo_propedeuticità_esame();

create or replace trigger iscrizione_esame
before insert on Esami_studenti
for each row execute function iscrizione_esame();

create or replace trigger delete_esame
before delete on esame
for each row execute function cancella_esame();

create or replace trigger update_esame
before update on esame
for each row
when(new.docente not like ('Docente rimosso %'))
execute function update_esame();

create or replace trigger inserire_esame
before insert on esame
for each row execute function inserire_esame();

alter table studenti enable row level security;
alter table utenti enable row level security;
alter table esami_studenti enable row level security;
alter table esame enable row level security;

create policy utenti_studente on utenti to studente
using (email=current_user or tipologia='docente');

create policy studenti_studenti on studenti to studente
using (email=current_user);

create policy studente_esami_studenti on esami_studenti to studente
using (studente = (select matricola from studenti where email=current_user) and esame_insegnamento= any (select esame_insegnamento from esame inner join insegnamento on esame.insegnamento = insegnamento.id where corso=(select frequenta from studenti)));

create policy utente_studente on studenti to studente
using (email = current_user);

create policy studente_esami_insegnamento on esame to studente
using (insegnamento = any( select id from studenti inner join corso on studenti.frequenta = corso.nome_corso
                  inner join insegnamento on corso.nome_corso = insegnamento.corso
                  where email=current_user));

create policy utenti_docenti on utenti to docente
using (tipologia = 'docente' or email = any (select distinct (email) from studenti ));

create policy studenti_docente on studenti to docente
using (frequenta in (select distinct (corso) from insegnamento inner join responsabile on insegnamento.id = responsabile.insegnamento where docente=current_user));

create policy esame_docente on esame to docente
using (insegnamento in (select distinct (insegnamento) from responsabile where docente=current_user));

create policy esami_studenti_docenti on esami_studenti to docente
using (esame_insegnamento= any(select id from Insegnamento inner join Responsabile on Insegnamento.id = Responsabile.insegnamento where docente= current_user));

create policy utenti_segretario on utenti to segretario
using(true);

create policy esame_segretario on studenti to segretario
using(true);

create policy studenti_segretario on esame to segretario
using(true);

create policy esame_studenti_segretario on esami_studenti to segretario
using(true);

revoke all privileges on all tables in schema public from visitatore;
revoke execute on all functions in schema public from studente,docente;
revoke all on all tables in schema public from studente,docente;
grant all on all tables in schema public to segretario;
grant select on all tables in schema public to docente,studente;
grant execute on function esiste_utente(varchar,text) to visitatore;
grant execute on function visualizza_carriera_valida(email varchar(120)), visualizza_carriera(email varchar(120)), controllo_lettera_esame(), elimina_esame_before() to studente;
grant select on esame,corso,laurea,insegnamento,propedeutico,responsabile to studente,docente;
grant insert,delete,select on esami_studenti to studente;
grant select on studenti to studente,docente;
grant select(email,nome,cognome,tipologia) on utenti to studente,docente;
grant select(email,nome,cognome,tipologia,cf) on utenti to segretario;
grant update(accettato) on esami_studenti to studente;
grant update(password) on utenti to studente,docente;
grant select on esami_studenti to docente;
grant insert on esame to docente;
grant update(data_ora) on esame to docente;
grant update(voto,lode) on esami_studenti to docente;
grant execute on function update_esame(),cancella_esame() to docente;
grant delete on esame to docente;
grant select,delete,insert on utenti_old to segretario;
grant all on all tables in schema public to segretario;
grant execute on function update_password() to segretario;