# EventHub – Event Management  #https://eventshub.free.nf/

Admin maintains the site and adds events; users register for events with multiple participants.

## Setup

1. **MySQL**: Create database and run schema.
   - Open phpMyAdmin or MySQL CLI.
   - Run `database/schema.sql` (creates DB `eventhub`, tables, seed events).
   - Then run: `php database/seed_admin.php` (creates admin: **admin@eventhub.local** / **Admin123!**).

2. **Config**: Edit `config/database.php`.
   - **base_path**: `''` if the app runs at docroot (e.g. `http://localhost/`). Use `/Event%20Website` (or your folder name) if under a subdir (e.g. XAMPP `htdocs/Event Website`).
   - **db**: Set `host`, `name`, `user`, `pass` for MySQL.

3. **Web server**: Point document root to this directory (or the parent). Ensure PHP and MySQL are enabled.

## Defaults

- **Admin**: admin@eventhub.local / Admin123!
- **Roles**: Admin (manage events, users) and User (browse, register with multiple participants).

## Flow

- **Admin**: Login → Dashboard → Create/Edit/Delete events, manage users.
- **User**: Register → Login → Events → Event detail → Register (add participants) → My Registrations.
