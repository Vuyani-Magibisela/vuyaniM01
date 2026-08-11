# MySQL init directory

Every `*.sql` file here runs, in filename order, the **first time** the
`db_data` volume is created. It does not re-run on later starts.

`10-seed.sql` is a snapshot of the production database. It contains real
contact submissions and subscriber email addresses, so it is git-ignored
and never leaves this machine.

## Recreating the seed after a fresh production export

```bash
sed -e '/^CREATE DATABASE/d' \
    -e '/^USE `/d' \
    -e 's/`vuyanjcb_vuyanim`\.//g' \
    path/to/new-dump.sql > 10-seed.sql
```

Then `..\..\..\dev.ps1 reseed` (or `make reseed`) to rebuild the database.

If this directory is empty, the stack still starts — you get an empty
database, and `dev.ps1 migrate` will build the schema from
`portfolio-website/database/migrations/`.
