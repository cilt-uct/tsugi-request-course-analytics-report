<?php
require_once('../../config.php');

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Roster;

// define('USERNAME', '01482222');
// define('PASSWORD', 'test');

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

$site_id = $LAUNCH->ltiRawParameter('context_id','none');
// $EID = $LAUNCH->ltiRawParameter('ext_d2l_username', $LAUNCH->ltiRawParameter('lis_person_sourcedid', $LAUNCH->ltiRawParameter('ext_sakai_eid', $USER->id)));
// lis_course_offering_sourcedid
$course_offering =  $LAUNCH->ltiRawParameter('lis_course_offering_sourcedid', 'none');
// var_dump($course_offering);
// check lms ext here
$lms_info = $LAUNCH->ltiRawParameter('tool_consumer_info_product_family_code');
$receipients_data = [];
// move auth to another file
function fetchWithBasicAuth($url, $userename, $password) {
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($userename . ':' . $password)
    ]);

    // Execute and get the response
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Error: ' . $error);
    }

    curl_close($ch);

    // Decode JSON response into an associative array
    $data = json_decode($response, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Decode Error: ' . json_last_error_msg());
    }

    return $data;
}

function filterByRole($receipients){
    // save this to database, for the admin, which roles,
    $desiredRoleIds = [110];

    // Filter function
    $filteredData = array_filter($receipients, function($item) use ($desiredRoleIds) {
        return isset($item['RoleId']) && in_array($item['RoleId'], $desiredRoleIds);
    });

    // Reset array keys
    return $filteredData;
}

if (str_contains($lms_info, 'sakai')) {
    // display any admin params needed here

    //get all recepients
    $receipients_data = $PDOX->allRowsDie("SELECT lti_user.user_id,lti_user.displayname, context_id, lti_user.email, JSON_UNQUOTE(ifnull(JSON_EXTRACT(lti_user.`json`,'$.sourcedId'), LOWER(SUBSTRING(lti_user.email, 1, LOCATE('@', lti_user.email) - 1)))) as eid, user_key FROM lti_user,lti_membership
    where lti_user.user_id=lti_membership.user_id and context_id={$CONTEXT->id} order by displayname");

} else if (str_contains($lms_info, 'desire2learn')){
    //get all recepients
    $query = "SELECT * FROM {$p}reports_ama_classlist_setting WHERE course_id = :course_id;";

    $arr = array(':course_id' => $site_id);
    $row = $PDOX->rowDie($query, $arr);
    $fullurl = $row['ama_classlisturl'] . '/' . $site_id;

    //$allreceipients = fetchWithBasicAuth('https://srvubuclt002.uct.ac.za/d2l/api/course/classlist/6824');
    $allreceipients = fetchWithBasicAuth($fullurl, $row['middleware_username'], $row['middleware_password']);

    $receipients_data = filterByRole($allreceipients['data']);

}

$OUTPUT->header();
$css = $CFG->getCurrentFileUrl('../css/app.css');
?>
    <link rel="stylesheet" type="text/css" href="<?= $css ?>"/>
<?php
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
echo("<h1>on-demand KYCS report</h1>\n");
$OUTPUT->welcomeUserCourse();

$settings_data = $PDOX->allRowsDie("SELECT * FROM reports_ama_classlist_setting
    where course_id = $site_id");

// roles
$db_roles = explode(',', $settings_data[0]['roles']);

?>
<!-- Form  -->

<?php if (str_contains($lms_info, 'desire2learn')){
    echo'<div class=pull-right><a href="#" data-toggle="modal" data-target="#exampleModal">Settings</a></div>
    ';
}?>
<form id="kycs_generate_form">
<div class="form-row align-items-center">
                <div class="col-auto">
                    <label for="course-input">Selected Course:</label>
                    <input id="course-input" type="text" class="form-control" name="all-courses" placeholder="Courses" value="<?= $site_id ?>" readonly>
                </div>
                <div class="col-auto">
                    <label for="semester-select">Semester:</label>
                    <select id="semester-select" class="form-control" name="semester">
                        <option value="First Semester">First Semester</option>
                        <option value="Second Semester">Second Semester</option>
                        <option value="Full Year">Full Year</option>
                    </select>
                </div>
            </div>
    <div class="form-group">
        <label for="course-input">Email to:</label>
        <div class="defaultmail"><?php echo $USER->email; ?></div>
        <div class="autocomplete-container">
            <div class="multiselect-tags" id="tags"></div>
            <input type="text" id="autocomplete-input" placeholder="search to email ..." value=''>
            <div id="autocomplete-list" class="autocomplete-items"></div>
        </div>
    </div>
    <input type="hidden" name="form_id" value="kycs_generate_form">
    <!-- <input type="hidden" name="all-emails" id="all-emails" value=""><br/> -->
    <input type="hidden" id="all-emails" name="all-emails" value='<?= json_encode([['email' => $USER->email, 'fullname' => $USER->firstname .' '. $USER->lastname]]) ?>'>
    <input type="hidden" name="requester_id" id="requester_id" value="<?php echo $USER->id; ?>"><br/>
    <input type="submit" name="send" value="Generate" onclick="submitForm('kycs_generate_form')">
</form>
<!-- create alert popupdiv -->
<div id="response"></div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">KYCS settings</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="kycs_settings_form">
            <div class="form-group">
                <label for="classlist-input">Classlists url:</label>
                <input id="classlist-input" type="text" name="classlist" placeholder="Brightspace Classlist" value="<?= $settings_data[0]['ama_classlisturl'] ?>">
            </div>
            <div class="form-group">
                <label for="username-input">Middleware username:</label>
                <input id="username-input" type="text" name="username" placeholder="Middleware username" value="<?= $settings_data[0]['middleware_username'] ?>">
            </div>
            <div class="form-group">
                <label for="password-input">Middleware password:</label>
                <input id="password-input" type="password" name="password" placeholder="Middleware password" value="<?= $settings_data[0]['middleware_password'] ?>">
            </div>
            <!-- roles select-->
            <div class="form-group">
            <label for="classlist-input">Roles:</label>
            <label class="col-md-1 checkbox-inline" for="checkboxes-0">
                <input type="checkbox" name="roles[]" id="checkboxes-0" value="109" <?= in_array(109, $db_roles) ? 'checked' : '' ?>>
                    Lecturer
            </label>
            <label class="col-md-1 checkbox-inline" for="checkboxes-1">
                <input type="checkbox" name="roles[]" id="checkboxes-1" value="126" <?= in_array(126, $db_roles) ? 'checked' : '' ?>>
                    Lecturer Tutor
            </label>
            <label class="col-md-1 checkbox-inline" for="checkboxes-2">
                <input type="checkbox" name="roles[]" id="checkboxes-2" value="122" <?= in_array(122, $db_roles) ? 'checked' : '' ?>>
                    Owner
            </label>
            <label class="col-md-1 checkbox-inline" for="checkboxes-3">
                <input type="checkbox" name="roles[]" id="checkboxes-3" value="114" <?= in_array(114, $db_roles) ? 'checked' : '' ?>>
                    Tutor
            </label>
            </div>
            <input type="hidden" name="course_id" value="<?= $site_id ?>">
            <input type="hidden" name="modified_by_id" value="<?= $USER->id ?>">
            <input type="hidden" name="modified_by" value="<?= $USER->displayname ?>">
            <input type="hidden" name="form_id" value="kycs_settings_form">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('kycs_settings_form')">Save changes</button>
      </div>
    </div>
  </div>
</div>
<?php
$OUTPUT->topNav();

$OUTPUT->footerStart();

?>
<script>

// jquery
$(document).ready(function() {
    // fetch data
    async function fetchData() {
        try {
            // Simulating PHP data; replace with actual fetch logic
            const data = <?php echo json_encode($receipients_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            return data;
        } catch (error) {
            console.error('There has been a problem with your fetch operation:', error);
            return [];
        }
    }

    function extractEmails(data) {
        try {
            // Check if data is an object
            if (typeof data !== 'object' || data === null) {
                throw new Error('Data is not an object');
            }

            // Initialize an array to store email addresses and full names
            const emailsWithNames = [];

            // Iterate over the properties of the data object
            for (const key in data) {
                // Check if the property is an object and has 'Email' and 'FirstName'/'Surname' properties
                if (typeof data[key] === 'object' && data[key] !== null && ('Email' in data[key] || 'email' in data[key])) {
                    const firstName = data[key].FirstName || data[key].firstName || '-';
                    const surname = data[key].Surname || data[key].surname || '-';
                    const fullName = `${firstName} ${surname}`;
                    // Add the email address and full name to the array
                    emailsWithNames.push({
                        email: data[key].Email || data[key].email,
                        fullname: fullName
                    });
                }
            }
            return emailsWithNames;
        } catch (error) {
            console.error('Error extracting emails:', error);
            return [];
        }
    }

    function initializeAutocompleteMultiselect(emailsWithNames, initialSelectedEmail = null) {
        // Check if emailsWithNames is null or undefined
        if (emailsWithNames === null || emailsWithNames === undefined) {
            console.error('Emails data is null or undefined');
            return; // Exit the function
        }

        // Proceed with setting up autocomplete if emailsWithNames is not null
        const availableItems = emailsWithNames.map(item => item.email);
        const selectedItems = [];
        let initialEmail = initialSelectedEmail ? initialSelectedEmail : $('#all-emails').val();

        if (initialEmail) {
            selectedItems.push(initialEmail);
        }

        const input = $("#autocomplete-input");
        const autocompleteList = $("#autocomplete-list");
        const tagsContainer = $("#tags");
        const hiddenInput = $("#all-emails");

        renderTags();

        input.on("input", function() {
            const query = this.value.split(',').pop().trim();
            autocompleteList.empty();

            if (!query) return;
            availableItems.forEach(item => {
                // Check if item and item.toLowerCase are not null before calling toLowerCase()
                if (item && item.toLowerCase && item.toLowerCase().includes(query.toLowerCase())) {
                    const listItem = $("<div>").text(item);
                    listItem.on("click", () => selectItem(item));
                    autocompleteList.append(listItem);
                }
            });
        });

        input.on("keydown", function(e) {
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
            input.val("");
            autocompleteList.empty();
        }

        function renderTags() {
            tagsContainer.empty();
            // Always include the initial email
            const uniqueItems = [...new Set([initialEmail, ...selectedItems.filter(item => item !== initialEmail)])];

            uniqueItems.forEach(item => {
                const matchedItem = emailsWithNames.find(emailWithName => emailWithName.email === item);
                const displayName = matchedItem ? `${matchedItem.fullname} (${matchedItem.email})` : item;
                const tag = $("<div>").addClass("multiselect-tag").text(displayName);
                //if (item !== initialEmail) {
                    const closeBtn = $("<span>").text("x");
                    closeBtn.on("click", () => removeItem(item));
                    tag.append(closeBtn);
                //}
                tagsContainer.append(tag);
            });

            // Update hidden input value with selected emails and full names as JSON
            updateHiddenInput();
        }

        function removeItem(item) {
            console.log(item)
            const index = selectedItems.indexOf(item);
            if (index > -1) {
                selectedItems.splice(index, 1);
            }
            renderTags();
        }

        function updateHiddenInput() {
            const selectedItemsWithNames = selectedItems.map(email => {
                const emailWithName = emailsWithNames.find(item => item.email === email);
                return emailWithName ? emailWithName : { email: email, fullname: '' };
            });
            hiddenInput.val(JSON.stringify(selectedItemsWithNames));
        }

        $(document).on("click", function(e) {
            if (!$(e.target).closest(".autocomplete-container").length) {
                autocompleteList.empty();
            }
        });
    }

    // Document ready function
    async function initialize() {
        const data = await fetchData(); // Assuming this function retrieves additional data

        // Extract emails with full names from fetched data (similar to your existing logic)
        const emailsWithNames = extractEmails(data);

        // Initialize autocomplete multiselect with emails and selected course email if exists
        initializeAutocompleteMultiselect(emailsWithNames, '<?php echo $USER->email; ?>');
    }

    // Call initialize function when document is ready
    initialize();
});

    // Form submission
    function submitForm(formId) {
        var formData = $('#' + formId).serialize();

        $.ajax({
            type: 'POST',
            url: 'form.php',
            data: formData,
            success: function(response) {
                console.log(response);  // Log the raw response
                var responseDiv = document.getElementById('response');
                try {
                    var jsonResponse = JSON.parse(response);
                    responseDiv.innerHTML = '<p>' + jsonResponse.message + '</p>';
                } catch (error) {
                    responseDiv.innerHTML = '<p>An error occurred: Invalid JSON response</p>';
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('response').innerHTML = '<p>An error occurred: ' + error + '</p>';
            }
        });
    }
</script>