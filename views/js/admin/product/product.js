$(document).ready(function () {
    $("#account_select").on('change', function () {
        changeAccountSelection();
    });
    
});

function showError(data, productName)
{

    var reference_message = '';
    modal.style.display = "block";
    var message = '<span><strong style="font-size: 16px;">Product: '+productName+'</strong></span>';
    message += "<table class='table table-bordered table-stripped'><thead style='background: #ECF6FB'>";
    message += "<tr><th><strong>Error</strong></th></tr></thead><tbody>";

    // if (data.length > 0) {
    //     for (var i in data) {
    //         reference_message = data[i].split(':');
            message += "<tr style='height: 30px;'><td style='font-weight: bold;'>"+data+"</td></tr>";
        // }
        message += "</tbody></table>";
    // }
    message += "</ul>";
    $("#popup_content_cednewegg").html(message);

}