<?php class_exists('Template') or exit; ?>
<!-- default table -->
<table class="table table-bordered table-rounded">
    <thead class="bg-info">
        <tr>
            <td style="width: 90vw;"> Course</td>
            <td style="width: 10vw;">Action</td>
        <tr>
    </thead>
            <td>
                <input id="course-input" type="text" class="form-control readonly-input" name="all-courses" placeholder="Courses" value="<?php echo $siteid ?>" readonly>
            </td>
            <td>
                <button type="button" class="reports-button btn btn-primary" data-toggle="modal" data-target="#kycsReportsModal">Send reports</button>
            </td>
        </tr>
</table>

<!-- results table -->
<!-- modal -->
<div class="modal fade" id="kycsReportsModal" tabindex="-1" role="dialog" aria-labelledby="kycsReportsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="kycs_generate_form" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="kycsReportsModalLabel">Email reports</h5>
                </div>
                <div class="modal-body" id="email_reports">
                    <div class="row">
                        <div class="col-xs-5">
                            <label for="semester-select">Semester:</label>
                            <select id="semester-select" class="form-control" name="semester" id="semester">
                                <option value="First Semester">First Semester</option>
                                <option value="Second Semester">Second Semester</option>
                                <option value="Full Year">Full Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5">
                            <select name="to[]" id="multiselect" class="form-control" size="8" multiple="multiple">
                            </select>
                        </div>

                        <div class="col-sm-2">
                            <button type="button" id="multiselect_rightAll" class="btn btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                            <button type="button" id="multiselect_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                            <button type="button" id="multiselect_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                            <button type="button" id="multiselect_leftAll" class="btn btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                        </div>

                        <div class="col-sm-5">
                            <select name="from[]" id="multiselect_to" class="form-control" size="8" multiple="multiple"></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="form_id" id="form_id" value="kycs_generate_form">
                    <input type="hidden" name="site_id" id="site_id" value="<?php echo $siteid ?>">
                    <input type="hidden" name="requester_id" id="requester_id" value="<?php echo $requesterid ?>">
                    <input type="hidden" name="doc_id" id="doc_id" value="<?php echo $bo_id ?>">
                    <button type="cancel" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btnSendReportsp" name="btnSendReports">Send Reports</button>
                </div>
            </form>
            <div id="response"></div>
        </div>
    </div>
</div>