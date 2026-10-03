# Portfolio + Admin Panel

My personal portfolio with a small admin panel I built to manage it.
Live (static version): **https://nihal-743.github.io**

Plain PHP + SQLite, hand-written CSS and JavaScript. No frameworks, no jQuery, no Bootstrap.

## What the admin can do

- Edit every text on the page (hero, about, typing words, contact, SEO)
- Add / edit / hide / delete projects with screenshot upload
- Manage experience, skills, "how I work" steps and education
- General settings: name, contact details, social links (empty ones don't show on the site)
- Read enquiries from the contact form, reply by email or WhatsApp
- Upload a new resume PDF
- Dashboard: site visits, unique visitors, resume views, traffic sources, devices, last 14 days chart

## Run it locally

1. Put the folder in `htdocs` (XAMPP) or any server with PHP 8+ and `pdo_sqlite`
2. Open `/admin` - first visit asks you to create your login
3. The database (`data/site.db`) is created automatically with the starting content

## Folder structure

```
index.php        the portfolio page (reads everything from the db)
enquiry.php      contact form saves here
cv.php           opens the resume and counts the view
admin/           admin panel
inc/             db connection, helpers, starting content
data/            sqlite database (git ignored)
img/  cv/        images and resume
```

## Security bits

- Passwords hashed with `password_hash`, login locks after 5 wrong tries
- CSRF token on every admin form, all queries are prepared statements
- Uploads checked by real file type, not just the extension
- `data/` and `inc/` are blocked by `.htaccess`
- Contact form has a honeypot + a 30 second limit against spam
