<?php class_exists('Template') or exit; ?>
<script src="<?php echo $script ?>" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function(){

    let responseData  = <?php echo $allrecepients ?>;
    //let responseData  = <?php echo json_encode($allrecepients) ?>;
    //console.log(typeof responseData);

    if (Array.isArray(responseData)) {
        //console.log(responseData);

        // Populate the select element with options
        let $multiselectTo = $('#multiselect_to');
        responseData.forEach(function(recipient) {
            let optionText = recipient.FirstName + " " + recipient.LastName + " (" + recipient.Email + ")";
            let optionValue = recipient.Email; // or any other unique identifier
            let $option = $('<option>')
                .val(optionValue)
                .text(optionText)
                .attr('data-firstname', recipient.FirstName)
                .attr('data-lastname', recipient.LastName);
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
        console.error('Data is not an array:', responseData);
    }
});

     // Form submission
     //function submitForm(formId) {
        $('#kycs_generate_form').on('submit', function(event) {

        event.preventDefault();


        var selectedOptions = [];
        $('#multiselect option').each(function() {
            var email = $(this).val();
            var firstname = $(this).data('firstname');
            var lastname = $(this).data('lastname');
            selectedOptions.push({ email: email, firstname: firstname, lastname: lastname });
            //selectedOptions.push($(this).val());
        });

        var formIdValue = $('#kycs_generate_form input[name="form_id"]').val();
        var requesterId = $('#kycs_generate_form input[name="requester_id"]').val();
        var siteIdValue = $('#kycs_generate_form input[name="site_id"]').val();
        var semesterValue = $('#kycs_generate_form select[name="semester"]').val();
        var bo_id = $('#kycs_generate_form input[name="doc_id"]').val();

        var selectedData = {
            'to': selectedOptions,
            'form_id': formIdValue,
            'requester_id': requesterId,
            'site_id': siteIdValue,
            'semester': semesterValue,
            'bo_id': bo_id
        };

        $.ajax({
            url: '<?php echo $kycsformurl ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(selectedData),
            success: function(response) {
                var success = response.success;
                var data = response.data;
                document.getElementById('response').innerHTML = '<p>Data: ' + data + '</p>';
                $('#kycsReportsModal').modal('hide');

                // Refresh the page
                window.location.reload();
            },
            error: function(xhr, status, error) {
                console.log("Error: ", error);
                document.getElementById('response').innerHTML = '<p>An error occurred: ' + error + '</p>';
            }
        });
    });



</script>