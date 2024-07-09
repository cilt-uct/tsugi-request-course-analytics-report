<?php class_exists('Template') or exit; ?>
    <script src="<?php echo $script ?>" type="text/javascript"></script>


<script type="text/javascript">
    $(document).ready(function(){

    let responseData  = <?php echo json_encode($allrecepients) ?>;

    let data = responseData.data;

    if (Array.isArray(data)) {
        // Populate the select element with options
        let $multiselectTo = $('#multiselect_to');
        data.forEach(function(recipient) {
            let optionText = recipient.FirstName + " " + recipient.LastName + " (" + recipient.Email + ")";
            let optionValue = recipient.Email; // or any other unique identifier
            let $option = $('<option>').val(optionValue).text(optionText);
            $multiselectTo.append($option);
        });

        // Initialize multiselect plugin if used
        $('#multiselect').multiselect({
            search: {
                left: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
                right: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
            },
            fireSearch: function(value) {
                return value.length > 3;
            }
        });
    } else {
        console.error('Data is not an array:', data);
    }



    });

     // Form submission
     function submitForm(formId) {
        let kycsFormUrl = '<?php echo $kycsformurl ?>';
        console.log(kycsFormUrl);

        var selectedOptions = [];
        $('#' + formId + ' #multiselect option').each(function() {
            var email = $(this).val();
            var fullname = $(this).text().split(' (')[0]; // Assuming the format is "Full Name (email)"
            selectedOptions.push({ email: email, fullname: fullname });
        });

        var formIdValue = $('#' + formId + ' input[name="form_id"]').val();
        var requesterId = $('#' + formId + ' input[name="requester_id"]').val();
        var siteIdValue = $('#' + formId + ' input[name="site_id"]').val();
        var semesterValue = $('#' + formId + ' #semester-select').val();

        var selectedData = {
            'to': selectedOptions,
            'form_id': formIdValue,
            'requester_id': requesterId,
            'site_id': siteIdValue,
            'semester': semesterValue
        };

        console.log("Selected Data: ", selectedData);

        $.ajax({
            type: 'POST',
            url: kycsFormUrl,
            contentType: 'application/json',
            data: JSON.stringify(selectedData),
            success: function(response) {
                alert(response);  // Log the raw response
                var responseDiv = document.getElementById('response');
                try {
                    var jsonResponse = JSON.parse(response);
                    alert(jsonResponse);
                    responseDiv.innerHTML = '<p>' + jsonResponse.message + '</p>';
                } catch (error) {
                    alert(error);
                    responseDiv.innerHTML = '<p>An error occurred: Invalid JSON response</p>';
                }
            },
            error: function(xhr, status, error) {
                alert(error);
                document.getElementById('response').innerHTML = '<p>An error occurred: ' + error + '</p>';
            }
        });
    }


</script>