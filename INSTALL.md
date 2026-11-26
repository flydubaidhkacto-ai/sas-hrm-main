# Appraisal module -> SAS HRM

Copy everything in this folder (except this file and the .patch) into the root of sas-hrm-main, overwriting:
  routes/web.php  (adds 'appraisal' to $modules)
  resources/views/sidebar/sidebar.blade.php  (adds menu item)
Or apply with git:  git apply appraisal-integration.patch

Then:
  php artisan migrate
  php artisan db:seed --class=AppraisalCriteriaSeeder
  php artisan serve
Login as super admin -> sidebar "Performance Appraisal" -> "Appraisal Setup" to assign roles/supervisors.
