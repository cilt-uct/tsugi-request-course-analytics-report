Request Course Analytics Report
=============================================================

A self service Tsugi tool with form request which generated on deman KYCS reports for course associated with site.

### UCT reference:
--------------
https://cilt.atlassian.net/browse/LA-84

### Required Paramaters
To generate KYCS Amathuba site should have course codes associated with it, if no course_codes are associated, a default message will appear.
"There are no course codes associated directly with this Amathuba site. To request a Know your Course and Students report for a specific course, please access this via the Amathuba site for the course, or submit a data query."

--------------
| Paramater | Description |
|-----------|-------------|
| course_code(s) | Course codes (providers_ids) associated with to Amathuba site |

The above passes the course code, Amathuba site id and current users email, full name to generate the email and pdf using the course_code. Requester can select multiple course_codes to generate these reports, depending on the codes linked to the Amathuba site.

Course code are then sent to BO Reports api, passing the course_code / provider_id, where it will generate a KYCS a schedule report which will be sent as soon as its generated and emailed.

Pre-Requisites
--------------

* Tsugi
* BO Reports - https://github.com/cilt-uct/bo-reports/

Sample text files for Course Analytics
=======================================================
data.json - kycs convenor sample list
