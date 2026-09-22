# Vercel deployment

This project uses the community PHP runtime for Vercel.

## 1. Create the database

Create a hosted MySQL database (Railway, Aiven, DigitalOcean, or another MySQL provider). Import `database/jersey_shop.sql` into it. A local XAMPP/MySQL database cannot be reached by a deployed Vercel function.

## 2. Import the project into Vercel

Push this project to GitHub, then choose **Add New Project** in Vercel and import the repository. Keep the project root as the repository root. No build command or output directory is needed.

## 3. Add environment variables

In Vercel project settings, add these variables for the Production environment:

`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, and `DB_NAME`

Use the values from the hosted MySQL provider. Do not commit real credentials.

## 4. Deploy

Deploy the project. The home page is `/index.php`; configure the Vercel domain as the public URL. The PHP runtime serves the existing root and `admin/` pages.

## Important production notes

- Vercel functions are stateless. PHP sessions may not persist reliably between requests, so production login/cart behavior should use a shared session store or a database-backed session handler.
- The Vercel filesystem is read-only at runtime. Admin image uploads to `assets/uploads/` will not persist; use object storage such as Cloudinary or UploadThing and save the returned URL in MySQL.
- `database/jersey_shop.sql` creates the schema, but admin credentials and product data still need to be created in the hosted database.