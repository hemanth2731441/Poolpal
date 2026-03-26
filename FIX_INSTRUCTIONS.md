# PoolPal - Database Fix & Google Places Instructions

## Issue Fixed: "Unknown column 'travel_date' in 'field list'"

### Problem
The application was trying to insert data into a `travel_date` column that didn't exist in the `ride_searches` table.

### Solution Implemented
1. **Updated `save_location.php`** - Now checks if the `travel_date` column exists before trying to insert data
2. **Created database fix script** - `database_fix.sql` to add the missing column
3. **Fixed Google Places API** - Improved initialization and error handling

## Database Fix Instructions

### Option 1: Using phpMyAdmin (Recommended)
1. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
2. Select the `ride_app` database
3. Click on the "SQL" tab
4. Copy and paste the following command:
```sql
ALTER TABLE `ride_searches` 
ADD COLUMN `travel_date` DATE NULL AFTER `to_lng`;
```
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
1. Open Command Prompt as Administrator
2. Navigate to your MySQL bin directory (usually `C:\wamp64\bin\mysql\mysql8.3.0\bin\`)
3. Run: `mysql -u root -p ride_app`
4. Execute: `ALTER TABLE ride_searches ADD COLUMN travel_date DATE NULL AFTER to_lng;`

### Option 3: Automatic Fix (Already Implemented)
The `save_location.php` file now automatically detects if the column exists and works either way.

## Google Places API Status

### ✅ Issues Fixed:
1. **Index.php** - Google Places autocomplete working
2. **Dashboard.php** - Search form with Places autocomplete working
3. **Tripdetails.php** - Location inputs with Places autocomplete working
4. **Error Handling** - Graceful fallback when API fails to load
5. **Form Validation** - Proper validation for location inputs
6. **Button Functionality** - All search/submit buttons now working properly

### Test Google Places API
1. Open `test_places_api.php` in your browser
2. This page will show the API status and allow testing
3. Try typing city names in the input fields
4. Verify that autocomplete suggestions appear

## Files Modified/Created

### Modified Files:
- `save_location.php` - Fixed database column issue
- `index.php` - Enhanced Google Places integration
- `dashboard.php` - Fixed search form and Places API
- `tripdetails.php` - Fixed Places autocomplete
- `config.php` - Added Google Maps configuration

### New Files Created:
- `database_fix.sql` - SQL script to add missing column
- `js/google-places-utils.js` - Centralized Google Places utility
- `test_places_api.php` - Testing page for Google Places API
- `FIX_INSTRUCTIONS.md` - This instruction file

## Testing Instructions

### 1. Test Database Fix
1. Try searching for rides on the homepage or dashboard
2. The search should complete without errors
3. Check that searches are saved in the `ride_searches` table

### 2. Test Google Places
1. Open any page with location inputs (index.php, dashboard.php, tripdetails.php)
2. Start typing a city name (e.g., "Bang" for Bangalore)
3. Verify autocomplete suggestions appear
4. Select a suggestion and verify it fills the input correctly

### 3. Test Form Submission
1. Fill out a complete search form
2. Click "Find Rides" or "Search Rides"
3. Verify the form submits successfully
4. Check that validation works for empty fields

## API Key Information
The application uses a Google Maps API key configured in `config.php` via `GOOGLE_MAPS_API_KEY`.

**Important**: This appears to be a demo/development key. For production use:
1. Get your own Google Maps API key from Google Cloud Console
2. Enable Places API and Maps JavaScript API
3. Update the key in `config.php`
4. Set up proper API restrictions and billing

## Troubleshooting

### If Places API Still Not Working:
1. Check browser console for JavaScript errors
2. Verify API key is valid and has Places API enabled
3. Check API quotas and billing in Google Cloud Console
4. Try the test page: `test_places_api.php`

### If Database Errors Persist:
1. Verify MySQL service is running in WAMP
2. Check database connection in `db.php`
3. Ensure `ride_app` database exists
4. Run the SQL fix manually in phpMyAdmin

### Form Validation Issues:
1. Clear browser cache
2. Check that JavaScript is enabled
3. Verify form field names match expectations
4. Check network tab for failed requests

## Success Indicators
- ✅ No "travel_date" database errors
- ✅ Google Places autocomplete working on all forms
- ✅ Search buttons functional
- ✅ Form validation working
- ✅ Proper error messages displayed
- ✅ Successful redirects after form submission

## Support
If you continue to experience issues:
1. Check browser console for errors
2. Verify WAMP services are running
3. Ensure all files are uploaded correctly
4. Test with the provided `test_places_api.php` page 