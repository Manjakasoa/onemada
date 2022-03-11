function hideCategoryField(){
    jQuery(jQuery(".tree-panel-heading-controls")[0].parentNode.parentNode.parentNode).addClass("hide");

}

function hideTotalProductField(){
    jQuery(jQuery("input#nbr_products")[0].parentNode.parentNode).addClass("hide");
}
function showTotalProductField(){
    jQuery(jQuery("input#nbr_products")[0].parentNode.parentNode).removeClass("hide");
}

function hideNbrLigneField(){
    jQuery(jQuery("input#nbr_rows")[0].parentNode.parentNode).addClass("hide");
}
function showNbrLigneField(){
    jQuery(jQuery("input#nbr_rows")[0].parentNode.parentNode).removeClass("hide");
}

function showCategoryField(){
    jQuery(jQuery(".tree-panel-heading-controls")[0].parentNode.parentNode.parentNode).removeClass("hide");
}
function showSelected_categoriesField(){
    jQuery(jQuery("input#selected_categories")[0].parentNode.parentNode).removeClass("hide");
}

function hideSelected_categoriesField(){
    jQuery(jQuery("input#selected_categories")[0].parentNode.parentNode).addClass("hide");
}
function showProductField(){
    jQuery(jQuery('input[name="product_codes"]')[0].parentNode.parentNode).removeClass("hide");
}
function hideProductField(){
    jQuery('input[name="product_codes"]').val("");
    jQuery(jQuery('input[name="product_codes"]')[0].parentNode.parentNode).addClass("hide");
}
jQuery(document).ready(function() {
     // hide category tree
     if(jQuery(".tree-panel-heading-controls").length>0){
        if(jQuery("#block_position").length==0)
            hideCategoryField();
    }
    
    if(jQuery('input[name="product_codes"]').length>0){
        
        if(jQuery("select[name='block_type']").val() != "custom"){
            hideProductField();
            hideSelected_categoriesField();
            showNbrLigneField();
            showTotalProductField();
        }
        if(jQuery("select[name='block_type']").val() == "selected_categories"){
            hideProductField();
            showSelected_categoriesField();
            showCategoryField();
            hideNbrLigneField();
            hideTotalProductField();
        }

        if(jQuery("select[name='block_type']").val() == "category"){
            hideProductField();
            showCategoryField();
            //showSelected_categoriesField();
            showNbrLigneField();
            showTotalProductField();
        }
        
    }

   

    jQuery(document).on('change', "select[name='block_type']", function() {
        if ( jQuery(this).val()=="custom" ) {
            hideCategoryField();
            showProductField();
           showTotalProductField();
           showNbrLigneField();
           hideSelected_categoriesField();
        } else if(jQuery(this).val()=="category"){
       
           hideProductField();
           showCategoryField();
           showTotalProductField();
           showNbrLigneField();
           hideSelected_categoriesField();
        }else if  (jQuery(this).val()=="selected_categories"){
            hideProductField();
           showCategoryField();
           hideTotalProductField();
           hideNbrLigneField();
           showSelected_categoriesField();
        }else{
            hideProductField();
            hideCategoryField();
            showTotalProductField();
            showNbrLigneField();
            hideSelected_categoriesField();
        }

    });

    jQuery("#category input[type=radio]").click(function(){
        if(jQuery("select[name='block_type']").val()=="selected_categories"){
            var selected = jQuery("input[name='selected_categories']").val();
            selected_categories = selected;
            if(selected.split(";").length<3){
            if(selected==""){
                selected_categories = jQuery(this).val();
            }else{
                selected_categories = selected_categories+";"+jQuery(this).val();;
            }
            jQuery("input[name='selected_categories']").val(selected_categories);
        }
        }
    });
});