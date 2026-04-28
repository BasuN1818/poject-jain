FROM php:8.2-apache

# Install required PHP extensions for MySQL connection
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite just in case it's needed
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# We copy the project files to a '1stProject' subdirectory.
# This ensures that hardcoded absolute paths in the code like '/1stProject/index.php' 
# continue to work exactly as they do in your local XAMPP environment.
COPY . /var/www/html/1stProject/

# Update permissions so Apache can read/write files (e.g., uploads or logs)
RUN chown -R www-data:www-data /var/www/html/1stProject

# Expose port 80
EXPOSE 80
