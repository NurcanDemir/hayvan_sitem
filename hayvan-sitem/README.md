# Hayvan Sitem Project

## Overview
Hayvan Sitem is a web application designed to facilitate the adoption of pets. The platform allows users to view active pet listings, register as users, and submit new pet listings for adoption. Admins have access to a dashboard for managing listings and overseeing the application.

## Project Structure
The project is organized into several directories, each serving a specific purpose:

- **public/**: Contains all publicly accessible files, including the main entry point and assets.
  - **index.php**: Main entry point for the application.
  - **ilanlar.php**: Displays all pet listings.
  - **ilan_detay.php**: Shows details of a specific pet listing.
  - **ilan_ekle.php**: Form for adding new pet listings.
  - **kayit.php**: Registration form for new users.
  - **giris.php**: Login form for existing users.
  - **assets/**: Contains CSS, JavaScript, and images.
    - **css/**: Styles for the application.
    - **js/**: Client-side JavaScript functionality.
    - **images/**: Images used in the application.

- **includes/**: Contains reusable components and configuration files.
  - **config.php**: Configuration settings for the application.
  - **db.php**: Database connection setup.
  - **header.php**: Header section included across multiple pages.
  - **footer.php**: Footer section included across multiple pages.
  - **functions.php**: Utility functions for the application.

- **admin/**: Contains files for the admin dashboard.
  - **index.php**: Entry point for the admin dashboard.
  - **dashboard.php**: Overview of the application for admins.
  - **manage-listings.php**: Admin functionality for managing pet listings.

- **uploads/**: Directory for storing uploaded images of animals.
  - **animals/**: Subdirectory for animal images.

- **database/**: Contains the SQL schema for setting up the database.
  - **schema.sql**: SQL schema file.

- **config/**: Contains database connection settings.
  - **database.php**: Database connection configuration.

## Getting Started
To get started with the Hayvan Sitem project, follow these steps:

1. **Clone the Repository**: Clone the project repository to your local machine.
2. **Set Up the Database**: Import the `schema.sql` file into your database to create the necessary tables.
3. **Configure Database Settings**: Update the `config/database.php` file with your database connection details.
4. **Run the Application**: Access the application through your web server (e.g., XAMPP) by navigating to the `public/index.php` file.

## Features
- User registration and login.
- Search functionality for pet listings.
- Admin dashboard for managing listings.
- Responsive design for mobile and desktop users.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License.