<?php
session_start();

// Load employee data
$file = 'employee.json';
$employees = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// Load religion data
$religionFile = 'religion.json';
$religions = file_exists($religionFile) ? json_decode(file_get_contents($religionFile), true) : [];
if (!is_array($religions)) {
    $religions = []; // Ensure it's an array even if file is empty or malformed
}

// Load designation data
$designationFile = 'designation.json';
$designations = file_exists($designationFile) ? json_decode(file_get_contents($designationFile), true) : [];
if (!is_array($designations)) {
    $designations = []; // Ensure it's an array even if file is empty or malformed
}

// Load states and cities data
$statesAndCitiesFile = 'statesandcities.json';
$statesAndCities = file_exists($statesAndCitiesFile) ? json_decode(file_get_contents($statesAndCitiesFile), true) : [];
if (!is_array($statesAndCities)) {
    $statesAndCities = []; // Ensure it's an array even if file is empty or malformed
}


// Generate next Employee ID
$lastId = 3000;
if (!empty($employees)) {
    // Find the maximum existing empid to ensure uniqueness
    $lastId = max(array_column($employees, 'empid'));
}
$nextId = $lastId + 1;


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); history.back();</script>";
        exit;
    }

    foreach ($employees as $employee) {
        if ($employee['email'] === $email) {
            echo "<script>alert('This email is already registered'); history.back();</script>";
            exit;
        }
    }

    // --- Start: Last Name STRICT Validation for "A" or "A B" (case-insensitive) ---
    $lname = trim($_POST['lname']); // Trim whitespace

    // This regex strictly checks for:
    // ^[a-zA-Z]$   -> a single alphabetic character (e.g., "A", "z")
    // |         -> OR
    // ^[a-zA-Z]\s[a-zA-Z]$ -> two alphabetic characters separated by a single space (e.g., "A B", "x Y")
    if (!preg_match('/^[a-zA-Z]$|^[a-zA-Z]\s[a-zA-Z]$/', $lname)) {
        echo "<script>alert('Last Name must be a single letter (a-z, A-Z) OR two letters separated by a single space (e.g., A B).'); history.back();</script>";
        exit;
    }
    // --- End: Last Name STRICT Validation ---


    // --- Start: Handle 'Other' for Religion (Revised logic) ---
    $selectedReligion = $_POST['religion_other'] ?? $_POST['religion'] ?? ''; // Prioritize _other input value
    
    // If a new religion was entered via 'other' input
    if (!empty($_POST['religion_other'])) {
        $newReligion = trim($_POST['religion_other']);
        if (!in_array($newReligion, $religions)) {
            $religions[] = $newReligion;
            sort($religions); // Keep it sorted
            file_put_contents($religionFile, json_encode($religions, JSON_PRETTY_PRINT));
        }
        // $selectedReligion is already set to $newReligion from the first line
    } elseif ($selectedReligion === 'Other') {
        // If "Other" was selected in dropdown but _other input was empty or not submitted
        $selectedReligion = '';
    }
    // --- End: Handle 'Other' for Religion ---


    // --- Start: Handle 'Other' for Designation (Revised logic) ---
    $selectedDesignation = $_POST['designation_other'] ?? $_POST['designation'] ?? ''; // Prioritize _other input value

    // If a new designation was entered via 'other' input
    if (!empty($_POST['designation_other'])) {
        $newDesignation = trim($_POST['designation_other']);
        if (!in_array($newDesignation, $designations)) {
            $designations[] = $newDesignation;
            sort($designations); // Keep it sorted
            file_put_contents($designationFile, json_encode($designations, JSON_PRETTY_PRINT));
        }
        // $selectedDesignation is already set to $newDesignation from the first line
    } elseif ($selectedDesignation === 'Other') {
        // If "Other" was selected in dropdown but _other input was empty or not submitted
        $selectedDesignation = '';
    }
    // --- End: Handle 'Other' for Designation ---


    // --- Start: Handle Country, State, City logic with "Other" (Revised logic) ---
    // Get values from POST, prioritizing 'other' inputs if they exist and are not empty
    $selectedCountry = $_POST['country_other'] ?? $_POST['country'] ?? '';
    $selectedState = $_POST['state_other'] ?? $_POST['state'] ?? '';
    $selectedCity = $_POST['city_other'] ?? $_POST['city'] ?? '';

    // Handle new country if entered via 'other' input
    if (!empty($_POST['country_other'])) {
        $newCountry = trim($_POST['country_other']);
        if (!array_key_exists($newCountry, $statesAndCities)) {
            $statesAndCities[$newCountry] = []; // Add new country with an empty state array
        }
        // $selectedCountry is already set to $newCountry
    } elseif ($selectedCountry === 'Other') {
        // If "Other" was selected in dropdown but _other input was empty or not submitted
        $selectedCountry = '';
    }

    // Handle new state if entered via 'other' input
    if (!empty($_POST['state_other'])) {
        $newState = trim($_POST['state_other']);
        // Ensure selectedCountry exists before adding a state to it
        if (!empty($selectedCountry) && !array_key_exists($newState, $statesAndCities[$selectedCountry])) {
            $statesAndCities[$selectedCountry][$newState] = []; // Add new state with an empty city array
        }
        // $selectedState is already set to $newState
    } elseif ($selectedState === 'Other') {
        // If "Other" was selected in dropdown but _other input was empty or not submitted
        $selectedState = '';
    }

    // Handle new city if entered via 'other' input
    if (!empty($_POST['city_other'])) {
        $newCity = trim($_POST['city_other']);
        // Ensure selectedCountry and selectedState exist before adding a city to it
        if (!empty($selectedCountry) && !empty($selectedState) && !in_array($newCity, $statesAndCities[$selectedCountry][$selectedState])) {
            $statesAndCities[$selectedCountry][$selectedState][] = $newCity;
            sort($statesAndCities[$selectedCountry][$selectedState]); // Keep cities sorted
            // Important: Re-index array after sorting and adding if it's a numeric array
            // If statesAndCities[$selectedCountry][$selectedState] is always numeric (cities are added with []),
            // then it should be fine. If it were associative, sorting would change keys.
        }
        // $selectedCity is already set to $newCity
    } elseif ($selectedCity === 'Other') {
        // If "Other" was selected in dropdown but _other input was empty or not submitted
        $selectedCity = '';
    }

    // Save the updated states and cities data
    file_put_contents($statesAndCitiesFile, json_encode($statesAndCities, JSON_PRETTY_PRINT));
    // --- End: Handle Country, State, City logic with "Other" ---

    // --- IMPORTANT CHANGE FOR DOB FORMAT ---
    $dob_raw = $_POST['dob'] ?? ''; // This will be YYYY-MM-DD from the HTML input
    $dob_formatted_for_json = '';
    if (!empty($dob_raw)) {
        // Convert YYYY-MM-DD to DD/MM/YYYY for storage in employee.json
        $dob_timestamp = strtotime($dob_raw);
        if ($dob_timestamp !== false) {
            $dob_formatted_for_json = date('d/m/Y', $dob_timestamp);
        }
    }
    // --- END IMPORTANT CHANGE ---


    $newEmployee = [
        'empid' => $nextId,
        'ename' => $_POST['ename'],
        'lname' => $lname, // Use the trimmed and validated value
        'mobileno' => $_POST['mobileno'],
        'email' => $email,
        'aadhar' => $_POST['aadhar'],
        'pincode' => $_POST['pincode'],
        'address' => $_POST['address'] ?? '',
        'country' => $selectedCountry,
        'state' => $selectedState,
        'city' => $selectedCity,
        'gender' => $_POST['gender'] ?? '',
        'dob' => $dob_formatted_for_json, // Use the formatted DOB for JSON
        'religion' => $selectedReligion,
        'designation' => $selectedDesignation,
    ];

    $employees[] = $newEmployee;
    file_put_contents($file, json_encode($employees, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $_SESSION['registration_success'] = true;
    header("Location: login.php");
    exit;
}

$showSuccess = isset($_SESSION['registration_success']);
unset($_SESSION['registration_success']);

// Get current year for DOB max year
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Registration</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 20px; }
        .container {
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 { color: #333; text-align: center; margin-bottom: 25px; }
        .success-message {
            color: #3c763d;
            background: #dff0d8;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        label.required:after { content: " *"; color: #d9534f; }

        /* Remove the dropdown arrow from all input types */
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            -webkit-appearance: none; /* Remove default appearance for webkit browsers */
            -moz-appearance: none;    /* Remove default appearance for mozilla browsers */
            appearance: none;         /* Remove default appearance */
            background-color: #fff;
            /* Explicitly remove the background image for inputs */
            background-image: none;
        }

        /* Keep the dropdown arrow for select elements */
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #fff;
            /* Keep the background image for selects */
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%204%205%22%3E%3Cpath%20fill%3D%22%23333%22%20d%3D%22M2%200L0%202h4zm0%205L0%203h4z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right .7em top 50%;
            background-size: .65em auto;
        }
        
        .toggle-optional {
            color: #4285f4;
            cursor: pointer;
            margin: 15px 0;
            display: inline-block;
        }
        .optional-section {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        button {
            background: #4285f4;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover { background: #3367d6; }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        /* Style for the "other" input field when it replaces the select */
        .select-as-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            margin-top: 5px; /* Adjust spacing if needed */
        }
    </style>

    <script>
    // General input restriction functions
    function restrictToNumbers(event) {
        const allowedKeys = [8, 9, 37, 38, 39, 40, 46]; // backspace, tab, arrow keys, delete
        if (allowedKeys.includes(event.keyCode) || 
            (event.keyCode >= 48 && event.keyCode <= 57) || // number keys
            (event.keyCode >= 96 && event.keyCode <= 105)) { // numpad keys
            return true;
        }
        event.preventDefault();
        return false;
    }

    // Allows alphabets and spaces (for Full Name)
    function restrictToAlphabets(event) {
        const allowedKeys = [8, 9, 32, 37, 38, 39, 40, 46]; // backspace, tab, space, arrow keys, delete
        if (allowedKeys.includes(event.keyCode) || 
            (event.keyCode >= 65 && event.keyCode <= 90)) { // A-Z
            return true;
        }
        event.preventDefault();
        return false;
    }

    // NEW: Specific function for Last Name client-side input restriction (A or A B format, case-insensitive)
    function restrictToLastnameFormat(event) {
        const key = event.key;
        const keyCode = event.keyCode;
        const input = event.target;
        const currentValue = input.value;

        // Allow control keys (backspace, tab, arrow keys, delete)
        const allowedControlKeys = [8, 9, 37, 38, 39, 40, 46];
        if (allowedControlKeys.includes(keyCode)) {
            return true;
        }

        // Convert key to uppercase for consistent comparison (though regex will be case-insensitive)
        const upperKey = key.toUpperCase();

        // If the current value is empty, only allow a letter
        if (currentValue.length === 0) {
            if (key.match(/^[a-zA-Z]$/)) { // Allow a-z or A-Z
                return true;
            }
        } 
        // If it has one character, allow a space or another letter (if current char is not already a space)
        else if (currentValue.length === 1) {
            if (currentValue.match(/^[a-zA-Z]$/)) { // Ensure first char is a letter
                if (key === ' ') { // Allow space for "A B" format
                    return true;
                } else if (key.match(/^[a-zA-Z]$/)) { // Allow another letter (e.g., if user types "AA" but we want to prevent it for "A B" format)
                    // If user types 'AA', this will prevent the second 'A'. We only want 'A' or 'A '
                    event.preventDefault(); 
                    return false;
                }
            }
        } 
        // If it has two characters, and the second is a space (e.g., "A "), allow another letter
        else if (currentValue.length === 2) {
            if (currentValue.match(/^[a-zA-Z]\s$/)) { // Ensure format is "A "
                if (key.match(/^[a-zA-Z]$/)) { // Allow a-z or A-Z
                    return true;
                }
            }
        }
        // Block any further input beyond 3 characters (e.g., "A B" is max 3 chars)
        else if (currentValue.length >= 3) {
            event.preventDefault();
            return false;
        }

        event.preventDefault(); // Block any other characters/invalid sequences
        return false;
    }

    // NEW: Specific function for Last Name paste (A or A B format, case-insensitive)
    function sanitizeAndEnforceLastnameFormat(event) {
        const clipboardData = event.clipboardData || window.clipboardData;
        let pastedText = clipboardData.getData('text/plain');
        
        event.preventDefault(); // Prevent default paste behavior

        // Sanitize: Remove anything not a-z, A-Z, or space
        pastedText = pastedText.replace(/[^a-zA-Z\s]/g, '');
        
        // Trim leading/trailing spaces and collapse multiple spaces to one
        pastedText = pastedText.trim().replace(/\s+/g, ' ');

        const input = event.target;
        let finalValue = '';

        // Check if the sanitized text matches the allowed patterns (case-insensitive)
        if (pastedText.match(/^[a-zA-Z]$/)) { // Matches "A" or "a" (single letter)
            finalValue = pastedText;
        } else if (pastedText.match(/^[a-zA-Z]\s[a-zA-Z]$/)) { // Matches "A B" or "a b" (two letters with space)
            finalValue = pastedText;
        }
        // If it doesn't match either of the strict formats, finalValue remains empty

        input.value = finalValue; // Set the input value to the strictly validated format
        input.selectionStart = input.selectionEnd = finalValue.length; // Place cursor at end
    }


    function allowOnlyNumbers(event) {
        const keyCode = event.which ? event.which : event.keyCode;
        
        if ([8, 9, 37, 38, 39, 40, 46].includes(keyCode)) { // Allow: backspace, tab, delete, arrow keys
            return true;
        }
        
        if ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105)) { // Allow only numbers (0-9)
            return true;
        }
        
        event.preventDefault(); // Prevent default for all other keys
        return false;
    }

    function preventNonNumericInput(event) {
        const charCode = event.which ? event.which : event.keyCode;
        
        // Allow control characters: backspace, delete, tab, arrows
        if (charCode > 31 && (charCode < 48 || charCode > 57) && (charCode < 96 || charCode > 105)) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    function sanitizePaste(event) {
        const clipboardData = event.clipboardData || window.clipboardData;
        const pastedText = clipboardData.getData('text/plain');
        const input = event.target;
        
        // Remove non-numeric characters for phone/aadhar/pincode
        const sanitizedText = pastedText.replace(/[^0-9]/g, ''); 
        
        // Prevent default paste and insert sanitized text
        event.preventDefault();
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const textBefore = input.value.substring(0, start);
        const textAfter = input.value.substring(end, input.value.length);
        
        input.value = textBefore + sanitizedText + textAfter;
        input.selectionStart = input.selectionEnd = start + sanitizedText.length;
    }


    // Helper function to create options
    function createOption(value, text) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;
        return option;
    }

    // Function to handle "Other" selection specifically for Religion, Designation, Country, State, and City
    function handleOtherSelect(field) {
        const selectElement = document.querySelector(`select[name="${field}"]`);
        const otherInputContainer = document.getElementById(`${field}InputContainer`);
        const otherInputField = document.getElementById(`${field}Input`);
        
        // Store the original name of the select element
        const originalSelectName = selectElement.dataset.originalName || selectElement.name;
        selectElement.dataset.originalName = originalSelectName; // Save it if not already saved

        if (selectElement.value === 'Other') {
            selectElement.style.display = 'none'; // Hide the select
            selectElement.removeAttribute('name'); // Remove name from select so it's not submitted
            otherInputContainer.style.display = 'block'; // Show the text input
            otherInputField.name = `${field}_other`; // Set name for form submission
            otherInputField.value = ''; // Clear previous value
            otherInputField.focus(); // Focus on the new input
        } else {
            selectElement.style.display = 'block'; // Show the select
            selectElement.name = originalSelectName; // Restore name to select
            otherInputContainer.style.display = 'none'; // Hide the text input
            otherInputField.removeAttribute('name'); // Remove name to avoid submitting empty 'other' field
        }
    }

    // Function to load states and cities based on selected country
    function loadStates() {
        const countrySelect = document.querySelector('select[name="country"]');
        const stateSelect = document.querySelector('select[name="state"]');
        const citySelect = document.querySelector('select[name="city"]'); 

        // Get 'Other' input containers for country, state, city
        const countryInputContainer = document.getElementById('countryInputContainer');
        const stateInputContainer = document.getElementById('stateInputContainer');
        const cityInputContainer = document.getElementById('cityInputContainer');

        const countryInputField = document.getElementById('countryInput');
        const stateInputField = document.getElementById('stateInput');
        const cityInputField = document.getElementById('cityInput');

        const selectedCountry = countrySelect.value;

        // Reset state and city dropdowns and hide 'Other' inputs
        stateSelect.innerHTML = '<option value="">-- Select State --</option>';
        citySelect.innerHTML = '<option value="">-- Select City --</option>';

        stateInputContainer.style.display = 'none';
        cityInputContainer.style.display = 'none';
        stateInputField.removeAttribute('name');
        cityInputField.removeAttribute('name');

        // Restore select elements' names (in case they were hidden and names removed)
        stateSelect.name = 'state';
        citySelect.name = 'city';
        stateSelect.style.display = 'block';
        citySelect.style.display = 'block';


        if (selectedCountry === 'Other') {
            countrySelect.style.display = 'none';
            countrySelect.removeAttribute('name'); // Remove name from country select
            countryInputContainer.style.display = 'block';
            countryInputField.name = 'country_other'; // Set name for submitting the new country
            countryInputField.value = '';
            countryInputField.focus();

            // When country is "Other", also hide state and city selects and show their "Other" inputs
            stateSelect.style.display = 'none';
            stateSelect.removeAttribute('name');
            stateInputContainer.style.display = 'block';
            stateInputField.name = 'state_other'; // Set name for submitting the new state
            stateInputField.value = '';

            citySelect.style.display = 'none';
            citySelect.removeAttribute('name');
            cityInputContainer.style.display = 'block';
            cityInputField.name = 'city_other'; // Set name for submitting the new city
            cityInputField.value = '';

            return; // No need to load states from JSON if "Other" country is selected
        } else {
            countrySelect.style.display = 'block';
            countrySelect.name = 'country'; // Restore name to country select
            countryInputContainer.style.display = 'none';
            countryInputField.removeAttribute('name');
        }

        // Fetch states from PHP-generated JSON (statesAndCities data)
        const statesAndCities = <?php echo json_encode($statesAndCities); ?>;

        if (selectedCountry && statesAndCities[selectedCountry]) {
            // Sort states for consistent display
            const sortedStates = Object.keys(statesAndCities[selectedCountry]).sort();
            sortedStates.forEach(state => {
                stateSelect.appendChild(createOption(state, state));
            });
        }
        stateSelect.appendChild(createOption('Other', 'Other')); // Always add "Other" option
    }

    // Function to load cities based on selected state
    function loadCities() {
        const countrySelect = document.querySelector('select[name="country"]');
        const stateSelect = document.querySelector('select[name="state"]');
        const citySelect = document.querySelector('select[name="city"]');

        const stateInputContainer = document.getElementById('stateInputContainer');
        const cityInputContainer = document.getElementById('cityInputContainer');

        const stateInputField = document.getElementById('stateInput');
        const cityInputField = document.getElementById('cityInput');

        const selectedCountry = countrySelect.value;
        const selectedState = stateSelect.value;

        citySelect.innerHTML = '<option value="">-- Select City --</option>'; // Clear cities

        cityInputContainer.style.display = 'none';
        cityInputField.removeAttribute('name');

        // Restore city select element's name (in case it was hidden and name removed)
        citySelect.name = 'city';
        citySelect.style.display = 'block';

        if (selectedState === 'Other') {
            stateSelect.style.display = 'none';
            stateSelect.removeAttribute('name'); // Remove name from state select
            stateInputContainer.style.display = 'block';
            stateInputField.name = 'state_other'; // Set name for submitting the new state
            stateInputField.value = '';
            stateInputField.focus();

            // If state is "Other", also hide city select and show city "Other" input
            citySelect.style.display = 'none';
            citySelect.removeAttribute('name'); // Remove name from city select
            cityInputContainer.style.display = 'block';
            cityInputField.name = 'city_other'; // Set name for submitting the new city
            cityInputField.value = '';

            return; // No need to load cities from JSON if "Other" state is selected
        } else {
            stateSelect.style.display = 'block';
            stateSelect.name = 'state'; // Restore name to state select
            stateInputContainer.style.display = 'none';
            stateInputField.removeAttribute('name');
        }

        // Fetch cities from PHP-generated JSON (statesAndCities data)
        const statesAndCities = <?php echo json_encode($statesAndCities); ?>;

        if (selectedCountry && selectedState && statesAndCities[selectedCountry] && statesAndCities[selectedCountry][selectedState]) {
            // Sort cities for consistent display
            const sortedCities = statesAndCities[selectedCountry][selectedState].sort();
            sortedCities.forEach(city => {
                citySelect.appendChild(createOption(city, city));
            });
        }
        // Always add "Other" option after populating with known cities
        citySelect.appendChild(createOption('Other', 'Other')); // Always add "Other" option
    }

    // Execute when the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // DOB Year Range (1900 to current year + 1)
        // This is now handled by PHP directly in the HTML input tag, so no JS needed here.
        
        // Load Religions dropdown
        const religionSelect = document.querySelector('select[name="religion"]');
        if (religionSelect) {
            <?php foreach ($religions as $r): ?>
                // Ensure "Other" isn't added twice if it's already in the JSON
                if ("<?php echo htmlspecialchars($r); ?>" !== "Other") {
                    religionSelect.appendChild(createOption("<?php echo htmlspecialchars($r); ?>", "<?php echo htmlspecialchars($r); ?>"));
                }
            <?php endforeach; ?>
            religionSelect.appendChild(createOption('Other', 'Other'));
        }

        // Load Designations dropdown
        const designationSelect = document.querySelector('select[name="designation"]');
        if (designationSelect) {
            <?php foreach ($designations as $d): ?>
                 // Ensure "Other" isn't added twice if it's already in the JSON
                if ("<?php echo htmlspecialchars($d); ?>" !== "Other") {
                    designationSelect.appendChild(createOption("<?php echo htmlspecialchars($d); ?>", "<?php echo htmlspecialchars($d); ?>"));
                }
            <?php endforeach; ?>
            designationSelect.appendChild(createOption('Other', 'Other'));
        }

        // Load Countries dropdown (initial load)
        const countrySelect = document.querySelector('select[name="country"]');
        if (countrySelect) {
            countrySelect.innerHTML = '<option value="">-- Select Country --</option>'; // Clear existing options
            const statesAndCities = <?php echo json_encode($statesAndCities); ?>;
            const sortedCountries = Object.keys(statesAndCities).sort();
            sortedCountries.forEach(country => {
                countrySelect.appendChild(createOption(country, country));
            });
            countrySelect.appendChild(createOption('Other', 'Other')); // Add "Other" option
            countrySelect.addEventListener('change', loadStates); // Attach event listener
        }

        // Attach event listener for state select
        const stateSelect = document.querySelector('select[name="state"]');
        if (stateSelect) {
            stateSelect.addEventListener('change', loadCities); // Attach event listener
        }

        // Attach event listener for city select (NEW) - Corrected Selector
        const citySelect = document.querySelector('select[name="city"]');
        if (citySelect) {
            citySelect.addEventListener('change', function() { handleOtherSelect('city'); }); // Attach event listener
        }


        // Ensure initial state of 'Other' input fields is hidden for religion, designation, country, state, city
        const fieldsToHideOtherInputs = ['religion', 'designation', 'country', 'state', 'city'];
        fieldsToHideOtherInputs.forEach(field => {
            const otherInputContainer = document.getElementById(`${field}InputContainer`);
            if (otherInputContainer) {
                otherInputContainer.style.display = 'none';
            }
        });
    });

    // Function for toggling optional fields visibility
    function toggleOptional() {
        const section = document.getElementById('optionalFields');
        const toggleText = document.querySelector('.toggle-optional');

        if (section.style.display === 'block') {
            section.style.display = 'none';
            toggleText.textContent = '▼ Show Optional Fields';
        } else {
            section.style.display = 'block';
            toggleText.textContent = '▲ Hide Optional Fields';
        }
    }
    </script>

</head>
<body>
<div class="container">
    <h2>Employee Registration</h2>

    <?php if ($showSuccess): ?>
        <div class="success-message">
            Registration successful! You can now login with your email.
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Employee ID: <strong><?php echo $nextId; ?></strong></label>
        </div>

        <div class="form-group">
            <label class="required">Full Name</label>
            <input type="text" name="ename" required onkeydown="restrictToAlphabets(event)" inputmode="text">
        </div>

        <div class="form-group">
            <label class="required">Last Name  <span style="font-size: 0.9em; color: #666;">(Initial Format Should be : A or A B)</span></label>
            <input type="text" name="lname" required 
                   onkeydown="restrictToLastnameFormat(event)" 
                   onpaste="sanitizeAndEnforceLastnameFormat(event)"
                   maxlength="3" 
                   pattern="[a-zA-Z]{1}|[a-zA-Z]\s[a-zA-Z]{1}" 
                   title="Last Name must be a single letter (e.g., A) OR two letters separated by a space (e.g., A B)." 
                   inputmode="text">
        </div>

        <div class="form-group">
            <label class="required">Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label class="required">Mobile Number</label>
            <input type="tel" name="mobileno" pattern="[0-9]{10}" title="10 digit mobile number" 
                required onkeydown="allowOnlyNumbers(event)" 
                onpaste="sanitizePaste(event)" 
                onkeypress="preventNonNumericInput(event)"
                maxlength="10" inputmode="numeric">
        </div>

        <div class="form-group">
            <label class="required">Aadhar Number</label>
            <input type="tel" name="aadhar" pattern="[0-9]{12}" title="12 digit Aadhar number" 
                required onkeydown="allowOnlyNumbers(event)" 
                onpaste="sanitizePaste(event)"
                onkeypress="preventNonNumericInput(event)"
                maxlength="12" inputmode="numeric">
        </div>

        <div class="form-group">
            <label class="required">Pincode</label>
            <input type="tel" name="pincode" pattern="[0-9]{6}" title="6 digit pincode" 
                required onkeydown="allowOnlyNumbers(event)" 
                onpaste="sanitizePaste(event)"
                onkeypress="preventNonNumericInput(event)"
                maxlength="6" inputmode="numeric">
        </div>

        <div class="toggle-optional" onclick="toggleOptional()">▼ Show Optional Fields</div>
        <div id="optionalFields" class="optional-section">
            
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address">
            </div>

            <div class="form-group">
                <label>Country</label>
                <select name="country" onchange="loadStates()">
                    <option value="">-- Select Country --</option>
                    </select>
                <div id="countryInputContainer" style="display:none; margin-top:10px;">
                    <input type="text" id="countryInput" class="select-as-input" placeholder="Enter country">
                </div>
            </div>

            <div class="form-group">
                <label>State</label>
                <select name="state" onchange="loadCities()">
                    <option value="">-- Select State --</option>
                    </select>
                <div id="stateInputContainer" style="display:none; margin-top:10px;">
                    <input type="text" id="stateInput" class="select-as-input" placeholder="Enter state">
                </div>
            </div>

            <div class="form-group">
                <label>City</label>
                <select name="city" onchange="handleOtherSelect('city')">
                    <option value="">-- Select City --</option>
                    </select>
                <div id="cityInputContainer" style="display:none; margin-top:10px;">
                    <input type="text" id="cityInput" class="select-as-input" placeholder="Enter city">
                </div>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">-- Select --</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" min="1900-01-01" max="<?php echo date('Y') + 1; ?>-12-31">
            </div>
            
            <div class="form-group">
                <label>Religion</label>
                <select name="religion" onchange="handleOtherSelect('religion')">
                    <option value="">-- Select Religion --</option>
                    </select>
                <div id="religionInputContainer" style="display:none; margin-top:10px;">
                    <input type="text" id="religionInput" class="select-as-input" placeholder="Enter religion">
                </div>
            </div>

            <div class="form-group">
                <label>Designation</label>
                <select name="designation" onchange="handleOtherSelect('designation')">
                    <option value="">-- Select Designation --</option>
                    </select>
                <div id="designationInputContainer" style="display:none; margin-top:10px;">
                    <input type="text" id="designationInput" class="select-as-input" placeholder="Enter designation">
                </div>
            </div>
        </div>

        <button type="submit">Register</button>
    </form>

    <div class="login-link">
        Already registered? <a href="login.php">Login here</a>
    </div>
</div>
</body>
</html>