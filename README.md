# IMPORTANTE
Progetto realizzato in PHP e PostgreSQL. Leggere "Relazione.pdf" nella directory "Relazione" per comprendere meglio il progetto.

# ATTENZIONE

Importando il file **DATABASE.sql** nel database eseguirà numerose volte le istruzioni
```sql
    1) execute 'create user '||quote_ident(new.email)||' with login encrypted password '||quote_literal(new.password);
    2) execute 'alter user '||quote_ident(new.email)||' with encrypted password '||quote_literal(new.password);
    3) execute 'drop user '||quote_ident(old.email);
```
per la creazione, modifica e cancellazione di tutti gli utenti nella tabella.
Se si esegue una *insert* nella tabella Utenti, verrà creato uno *user* con è possibile accedervi direttamente dal terminale testuale, senza la necessità di usare l'applicazione web.

Se si vuole cancellare il Database in maniera correnta, dopo aver cancellato tutte le tabelle, funzioni, tipi di dato e policy di accesso dei dati, eseguire il seguente blocco di codice:
```sql
create or replace function delete_user() returns void as $$
    declare
        rec record;
    begin
        for rec in (select grantee from information_schema.applicable_roles where role_name in ('docente','segretario','studente'))
        loop
            execute 'drop user ' ||quote_ident(rec.grantee);
        end loop;
    end;
$$ language plpgsql;

select *
from delete_user();

drop role segretario;

drop role docente;

drop role studente;

drop role visitatore;
drop user accesso;

drop function delete_user();
```

## INSTALLAZIONE
Per importare il file **DATABASE.sql** importarlo in un database con nome *progetto*. Utilizzare un utente con poteri di admin per importare il database, altrimenti alcune funzioni non potranno essere eseguite.<br>
Tutte le password degli user sono 1234.
<br><br>
La pagina **index.php** porterà alla schermata di login.

### ATTENZIONE
Quando si esegue l'update della password, da terminarle, eseguirlo come se fosse una transazione in questo modo:
```sql
    begin;
        update utenti set password ='...' where email=current_user; 
    commit;
```
