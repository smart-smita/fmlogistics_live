<?php 
$form_id= $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>
<?php $this->extend("Layout/base_admin"); ?>
<?php $this->section("breadcrumb_title_li"); ?>
<!--begin::Item-->
<li class="breadcrumb-item text-muted">
    <a><?=isset($title)?$title:"Sub Category"?></a>
</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>
<?=$table?>
<?php $this->endSection(); ?>

<?php $this->section("modals_section"); ?>
<!--begin::Modal - Create App-->
<div class="modal fade" id="<?=$button_id?>" tabindex="-1" aria-hidden="true">
    <form id="<?=$form_id?>_form" onsubmit="return false;">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2>Sub Category Details</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                    <span class="svg-icon svg-icon-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                            <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body py-lg-10 px-lg-10">
                <div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#fees_head" data-kt-scroll-offset="300px" style="max-height: 273px;">
                    <!--begin::Input group-->
                    <div class="row mb-12">

                        <!--begin::Col-->
                        <div class="col-md-12 fv-row fv-plugins-icon-container">
                            <label class="required fs-5 fw-bold-2">Site Category</label>
                            <select class="form-select" name="site_category_id" id="site_category_id">
                                <option value="">Select Site Category</option>
                                <?php if(isset($site_category_list)): ?>
                                    <?php foreach($site_category_list as $cat): ?>
                                        <option value="<?=$cat['site_category_id']?>"><?=$cat['site_category_name']?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-12 fv-row fv-plugins-icon-container mt-4">
                            <label class="required fs-5 fw-bold-2">Sub Category Name</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Sub Category Name" id="sub_category_name" name="sub_category_name">
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                        <!--end::Col-->
                        
                    </div>
                    <!--end::Input group-->
                </div>
            </div>
            <!--end::Modal body-->
            
            <!--begin::Modal footer-->
            <div class="modal-footer flex-center">
                <!--begin::Button-->
                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                <!--end::Button-->
                
                <!--begin::Button-->
                <button type="submit" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">
                    <span class="indicator-label">Submit</span>
                    <span class="indicator-progress">Please wait... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <!--end::Button-->
            </div>
            <!--end::Modal footer-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
    </form>
</div>
<!--end::Modal - Create App-->

<?php $this->endSection();?>
	
<?php $this->section("javascript_section");?>
<script>
const form = document.getElementById('<?=$form_id?>_form');
var validation_object  = {
    fields: {
        'site_category_id': {
            validators: {
                notEmpty: {
                    message: 'Site Category is required'
                }
            }
        },
        'sub_category_name': {
            validators: {
                notEmpty: {
                    message: 'Sub Category Name is required'
                }
            }
        }
    },
    plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap: new FormValidation.plugins.Bootstrap5({
            rowSelector: '.fv-row',
            eleInvalidClass: '',
            eleValidClass: ''
        })
    }
};
var validator = FormValidation.formValidation(form,validation_object);

$("#ajax_click").on("click",function(){
    var button = $(this);
    var url = $(this).attr("data-ajax-url");
    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                var data_to_send = $("#<?=$form_id?>_form").serializeArray();
                var formData = {};
                $.each(data_to_send, function(i, field) {
                    if (field.value.trim() !== "") {
                        formData[field.name] = field.value;
                    }
                });

                ajax_call(url,formData,button,function(responce){
                    try{
                        responce = JSON.parse(responce);
                        if(responce.status==1){
                            toastr.success(responce.message);
                            form.reset();
                            $("#<?=$form_id?>").modal("hide");
                            reload_data_table();
                        }else{
                            toastr.warning(responce.message);
                        }
                    }catch(error){
                        toastr.error(error);
                    }
                });
            }
        });
    }
});

function edit_id(obj,id){
    var url = $(obj).attr("data-ajax-url");
    var formData={"id":id};
    ajax_call(url,formData,$(obj),function(responce){
        try{
            responce = JSON.parse(responce);
            if(responce.status==1){
                toastr.success(responce.message);
                $("#<?=$form_id?>").find("#site_category_id").val(responce.data.site_category_id);
                $("#<?=$form_id?>").find("#sub_category_name").val(responce.data.sub_category_name);
                $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url",$("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url")+"/"+id);
                $("#<?=$form_id?>").modal("show");
            }else{
                toastr.warning(responce.message);
            }
        }catch(error){
            toastr.error(error);
        }
    });
}

function delete_row(obj,id){
    Swal.fire({
        title: 'Do you want delete?',
        showCancelButton: true,
        confirmButtonText: 'Save',
    }).then((result) => {
        if (result.isConfirmed) {
            url_call_ajax($(obj).attr("data-ajax-url"),$(obj));
        } 
    });
}
</script>
<?php $this->endSection();?>
