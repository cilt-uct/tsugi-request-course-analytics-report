<?php
require_once('../config.php');

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

// get the course by context on AMA,
$course_code = 'ECO1011S_55';

// Check if the form is submitted
if ( isset($_POST['send'])) {
// Retrieve and sanitize the form data
$courses = isset($_POST['all-courses']) ? htmlspecialchars($_POST['all-courses']) : '';
$emails = isset($_POST['all-emails']) ? htmlspecialchars($_POST['all-emails']) : '';

// Validate form data
if (empty($courses) || empty($emails)) {
        $_SESSION['error'] = "Please fill out all required fields.";
        header('Location: '.addSession('index.php'));
        return;
} else {
        // add to table
        $stmt = $PDOX->prepare("INSERT INTO {$p}kycs_reports_on_demand
        (requester_id, course_codes, email_to, created_at)
        VALUES (:RI, :CC, :EM, NOW())");

        // Bind parameters
        $stmt->bindParam(':RI', $USER->id); // Assuming $USER->id is the requester ID
        $stmt->bindParam(':CC', $courses);
        $stmt->bindParam(':EM', $emails);

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['success'] = "Report has been generated";
            echo json_encode(['status' => 'success', 'message' => 'Report has been generated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
        $_SESSION['success'] = "Report has been generated";
    }
}


$OUTPUT->header();
$css = $CFG->getCurrentFileUrl('css/app.css');
?>
    <link rel="stylesheet" type="text/css" href="<?= $css ?>"/>
<?php
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
echo("<h1>on-demand KYCS report</h1>\n");
$OUTPUT->welcomeUserCourse();

?>

<!-- initial generate button -->
<button type="button" class="btn btn-primary" type="button" data-toggle="collapse" data-target="#FormCollapse" aria-expanded="false" aria-controls="FormCollapse">Generate Form</button>
<!-- Form  -->
<div class="collapse" id="FormCollapse">
    <form method="post" id="kycsForm">
        <div class="form-group">
            <label for="course-input">Selected Course:</label>
            <input id="course-input" type="text" name="all-courses" placeholder="Courses" readonly>
        </div>
        <div class="form-group">
            <label for="course-input">Emails:</label>
            <div class="autocomplete-container">
                <div class="multiselect-tags" id="tags"></div>
                <input type="text" id="autocomplete-input" placeholder="search to email ...">
                <div id="autocomplete-list" class="autocomplete-items"></div>
            </div>
        </div>
        <input type="hidden" name="all-emails" id="all-emails"><br/>
        <input type="submit" name="send" value="Generate">
    </form>
    </div>
</div>
<!-- create alert popupdiv -->
<div id="response"></div>
<?php
$OUTPUT->topNav();

$OUTPUT->footerStart();

?>
<!-- <script src="<?php echo $CFG->staticroot; ?>/scripts/jquery.autocomplete.multiselect.js"></script> -->
<script>

    // Function to fetch data from JSON file
async function fetchData() {
    try {
        const response = await fetch('data.json');
        if (!response.ok) {
            throw new Error('Network response was not ok ' + response.statusText);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return [];
    }
}

// Function to extract emails from data
function extractEmails(data) {
    try {
        // Extract emails from the data
        const emails = data.map(item => item['Course Convenor Email']).filter(email => email !== undefined);
        return emails;
    } catch (error) {
        console.error('Error extracting emails:', error);
        return []; // Return an empty array if there is an error
    }
}

// Function to extract current course details
function extractCurrentCourseDetails(data, courseCode) {
    let courseFound = false;
    let courseEmail = '';
    data.forEach(function(course) {
        if (course['Course Code'] === courseCode) {
            courseFound = true;
            courseEmail = course['Course Convenor Email'];
            return false; // Exit the loop early if match is found
        }
    });

    // Display course convenor's email if match was found
    if (courseFound) {
        console.log('Course Convenor Email:', courseEmail);
        document.querySelector('input#course-input').value = courseCode;
        document.querySelector('#tags').value = courseEmail;
        // Display or use the course convenor's email as needed
        return courseEmail;
    } else {
        console.log('Course not found in data.'); // Handle case where course code is not found
        return null;
    }
}

// Function to initialize autocomplete and multiselect
function initializeAutocompleteMultiselect(emails, initialSelectedEmail = null) {
    const availableItems = emails;
    const selectedItems = initialSelectedEmail ? [initialSelectedEmail] : [];

    const input = document.getElementById("autocomplete-input");
    const autocompleteList = document.getElementById("autocomplete-list");
    const tagsContainer = document.getElementById("tags");
    const hiddenInput = document.getElementById("all-emails");

    renderTags();

    input.addEventListener("input", function() {
        const query = this.value.split(',').pop().trim();
        autocompleteList.innerHTML = "";

        if (!query) return;

        availableItems.forEach(item => {
            if (item.toLowerCase().includes(query.toLowerCase())) {
                const listItem = document.createElement("div");
                listItem.innerText = item;
                listItem.addEventListener("click", () => selectItem(item));
                autocompleteList.appendChild(listItem);
            }
        });
    });

    input.addEventListener("keydown", function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const emails = this.value.split(',').map(email => email.trim()).filter(email => email !== '');
            emails.forEach(email => {
                if (email && !selectedItems.includes(email) && availableItems.includes(email)) {
                    selectedItems.push(email);
                }
            });
            this.value = '';
            renderTags();
        }
    });

    function selectItem(item) {
        if (!selectedItems.includes(item)) {
            selectedItems.push(item);
            renderTags();
        }
        input.value = "";
        autocompleteList.innerHTML = "";
    }

    function renderTags() {
        tagsContainer.innerHTML = "";
        selectedItems.forEach(item => {
            const tag = document.createElement("div");
            tag.classList.add("multiselect-tag");
            tag.innerText = item;
            const closeBtn = document.createElement("span");
            closeBtn.innerText = "x";
            closeBtn.addEventListener("click", () => removeItem(item));
            tag.appendChild(closeBtn);
            tagsContainer.appendChild(tag);
        });
        // Update hidden input value with selected emails
        hiddenInput.value = selectedItems.join(',');
    }

    function removeItem(item) {
        const index = selectedItems.indexOf(item);
        if (index > -1) {
            selectedItems.splice(index, 1);
        }
        renderTags();
    }

    document.addEventListener("click", function(e) {
        if (!e.target.closest(".autocomplete-container")) {
            autocompleteList.innerHTML = "";
        }
    });
}

// Main function to orchestrate fetching data and initializing components
    async function main() {
            const data = await fetchData();

            // Extract emails from data
            const emails = extractEmails(data);

            // Extract current course details
            const courseCode = '<?php echo $course_code; ?>';
            const courseEmail = extractCurrentCourseDetails(data, courseCode);

            // Initialize autocomplete and multiselect with emails
            initializeAutocompleteMultiselect(emails, courseEmail);
        }
// Form submission
document.getElementById('kycsForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting the traditional way

    var courses = document.getElementById('course-input').value;
    var emails = document.getElementById('all-emails').value;

    var xhr = new XMLHttpRequest();
    // TODO - Check this
    xhr.open('POST', 'index.php', true);  //
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var responseDiv = document.getElementById('response');
            if (response.status === 'success') {
                responseDiv.innerHTML = '<p>' + response.message + '</p>';
            } else {
                responseDiv.innerHTML = '<p>' + response.message + '</p>';
            }
        } else if (xhr.readyState === 4) {
            document.getElementById('response').innerHTML = '<p>An error occurred: ' + xhr.statusText + '</p>';
        }
    };

    var data = JSON.stringify({
        courses: courses,
        emails: emails
    });

    xhr.send(data);
});
// Call the main function to kick off the process
main();

</script>