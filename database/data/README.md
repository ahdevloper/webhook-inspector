# Geographic data

The full GeoNames dump is intentionally not committed to the repository because the worldwide allCountries.zip archive is large and changes frequently.

The importer downloads the current open dump into storage/app/geo and imports every GeoNames record with feature class P (populated place), including cities, towns, villages, localities and administrative seats. GeoNames describes class P as populated-place features and publishes the complete worldwide dump under CC BY 4.0.

Run:
php artisan migrate
php artisan geo:import

For later refreshes:
php artisan geo:update

The importer also reads UN M49 for region and subregion classification. Source URLs and the license are kept in config/geo.php.
