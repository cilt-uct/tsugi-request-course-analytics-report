<?php class_exists('Template') or exit; ?>
<div class="flex-container">
    <?php foreach($reports as $rep): ?>
        <?php if($rep["active"]) { ?>
            <?php if ($rep['target']) { ?>
            <a href="#" data-toggle="modal" data-target="#<?php print($rep['target']) ?>">
                <img src="<?php print($rep['img']) ?>" class='report-image'>
                <h5>"<?php print($rep['title']) ?>"</h5>
            </a>
            <?php } else { ?>
            <a href="<?php print($rep['url']) ?>">
                <img src="<?php print($rep['img']) ?>" class='report-image'>
                <h5>"<?php print($rep['title']) ?>"</h5>
            </a>
    <?php } ?>
    <?php } ?>
    <?php endforeach; ?>
</div>

<!-- data reports -->
<button class="btn btn-primary btn-chevron collapsed" type="button" data-toggle="collapse" data-target="#previousReports" aria-expanded="false" aria-controls="previousReports">
    View reports data
</button>
</p>
<div class="collapse" id="previousReports">
    <div class="card card-body">
        <table class="table table-bordered table-rounded">
            <thead class="bg-info">
                <tr>
                    <td style="width: 10vw;"> Requestor</td>
                    <td style="width: 10vw;">Recepients</td>
                    <td style="width: 10vw;">Status</td>
                    <td style="width: 15vw;">Report Type</td>
                    <td style="width: 10vw;">Date Generated</td>
                    <td style="width: 10vw;">Date Sent</td>
                <tr>
            </thead>
            <?php foreach($past_reports as $rep): ?>
            <tr>
                <td><?php print($rep['requester_name']) ?></td>
                <td class="emails"><?php print($rep['data']) ?></td>
                <td><?php print($rep['state']) ?></td>
                <td><?php print($rep['report_type']) ?></td>
                <td><?php print($rep['created_at']) ?></td>
                <td><?php print($rep['modified_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<!-- Kycs modal -->
<!-- modal -->
<div class="modal fade" id="kycsReportsModal" tabindex="-1" aria-labelledby="kycsReportsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="kycs_generate_form" method="POST">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="kycsReportsModalLabel">KYCS reports</h5>
                    </div>
                    <div>
                        <span class="font-weight-bold">Course Code:</span> <?php echo $siteid ?>
                    </div>
                    <div>
                        <select class="selectpicker" id="providers" data-live-search="true" multiple>
                          </select>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="email_reports">
                    <div class="row">
                        <div class="col-sm-5">
                            <label>Email to:</label>
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
                            <label>Available:</label>
                            <select name="from[]" id="multiselect_to" class="form-control" size="8" multiple="multiple"></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="form_id" id="form_id" value="kycs_generate_form">
                    <input type="hidden" name="site_id" id="site_id" value="<?php echo $siteid ?>">
                    <input type="hidden" name="requester_id" id="requester_id" value="<?php echo $requesterid ?>">
                    <input type="hidden" name="requester_fullname" id="requester_fullname" value="<?php echo $requester_fullname ?>">
                    <input type="hidden" name="doc_id" id="doc_id" value="<?php echo $bo_id ?>">
                    <button type="cancel" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btnSendReportsp" name="btnSendReports">Send Reports</button>
                </div>
            </form>
            <div id="response"></div>
        </div>
    </div>
</div>