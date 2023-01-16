$(document).ready(function () {
    $("#account-select").on('change', function () {
        fetchNeweggCategoryDetails();
    });
    $("#profile-category").on('change', function () {
        getRequiredAttributes();
    });

  var buttonAdd = $("#add-button");
  var buttonRemove = $("#remove-button");
  var className = ".dynamic-field";
  var count = 0;
  var field = "";
  var maxFields =50;

  buttonAdd.click(function() {
    addNewField();
  });
  
  buttonRemove.click(function() {
    removeLastField();
  });
});
function totalFields() {
    return $(".dynamic-field").length;
  }

  function addNewField() {
    field =  document.getElementById("dynamic-field-default");
    field_first_td = field.getElementsByTagName("td")[0].children[0];
    field_second_td = field.getElementsByTagName("td")[1].children[0];
    field_third_td = field.getElementsByTagName("td")[2].children[0];
    $('#attributes-newegg').append(`<tr><td>${field_first_td.outerHTML}</td><td>${field_second_td.outerHTML}</td><td>${field_third_td.outerHTML}</td></tr>`);
    count = document.getElementsByClassName("dynamic-field-optional").length;
    field =  document.getElementById("attributes-newegg").lastElementChild;
    field.setAttribute("id", "dynamic-field-" + count);
    field_first_td = field.getElementsByTagName("td")[0].children[0];
    field_second_td = field.getElementsByTagName("td")[1].children[0];
    field_third_td = field.getElementsByTagName("td")[2].children[0];
    field_first_td.setAttribute('name',`newegg_opt_attributes[${count}][name]`);
    field_second_td.setAttribute('name',`newegg_opt_attributes[${count}][presta_attr_code]`);
    field_third_td.setAttribute('name',`newegg_opt_attributes[${count}][presta_attr_code]`);
  }

  function selectDefault(obj){
    
     if(obj.value=='--Set Default Value--'){
        var nameAttr=obj.name.split('[');
        nameAttr=nameAttr[0]+'['+nameAttr[1]+'['+'default]';
        obj.parentElement.parentElement.lastElementChild.children[0].setAttribute('type','text');
        obj.parentElement.parentElement.lastElementChild.children[0].setAttribute('name',`${nameAttr}`);
     }
     else{
      obj.parentElement.parentElement.lastElementChild.children[0].setAttribute('type','hidden');
     }
   }

  function removeLastField() {
    if(document.getElementById("attributes-newegg").children.length > ($(".req_attr").length+12))
     {
       document.getElementById("attributes-newegg").lastElementChild.remove();
     }
  }

  function disableButtonAdd() {
    if (totalFields() === 50) {
      buttonAdd.attr("disabled", "disabled");
      buttonAdd.removeClass("shadow-sm");
    }
  }

  function enableButtonAdd() {
    if (totalFields() === (50 - 1)) {
      buttonAdd.removeAttr("disabled");
      buttonAdd.addClass("shadow-sm");
    }
  }

function getRequiredAttributes(){
         $.ajax({
                type: 'POST',
                url: 'ajax-tab.php',
                data: {
                    controller: 'AdminCedNeweggProfile', /* better lowercase 'category' */
                    ajax : true,
                    action : 'getRequiredAttributes',
                    newegg_account_id: $("#account-select").val(),
                    newegg_sub_cat: $('#profile-category').val(),
                    token : $('#token-newegg').val()
                },
                success: function (res) {
                    var data = JSON.parse(res);
                    document.getElementById('attributes-newegg').innerHTML = data.content;
                }
            });
}

function fetchNeweggCategoryDetails()
{
    accountId = $('#account-select').val();
         $.ajax({
                type: 'POST',
                url: 'ajax-tab.php',
                data: {
                    controller: 'AdminCedNeweggProfile', /* better lowercase 'category' */
                    ajax : true,
                    action : 'fetchNeweggCategoryDetails',
                    newegg_account_id: $("#account-select").val(),
                    token : $('#token-newegg').val()
                },
                success: function (res) {
                    var data = JSON.parse(res);
                    console.log(data);
                    $("#profile-category").empty();
                    for(i=0;i<data['newegg_categories'].length;i++){
                        $("#profile-category").append(`<option value='${data['newegg_categories'][i]['sub_cat_Id']}:${data['newegg_categories'][i]['sub_cat_name']}'>${data['newegg_categories'][i]['sub_cat_name']}</option>`);
                    }                  
                }
            });

}

