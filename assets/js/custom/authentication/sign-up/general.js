"use strict";
var KTSignupGeneral = (function () {
    var e, t, a, r;
    return {
        init: function () {
            (e = document.querySelector("#kt_sign_up_form")),
                (t = document.querySelector("#kt_sign_up_submit")),
                (r = KTPasswordMeter.getInstance(e.querySelector('[data-kt-password-meter="true"]'))),
                (a = FormValidation.formValidation(e, {
                    fields: {
                        "first_name": { validators: { notEmpty: { message: "First Name is required" } } },
                        "last_name": { validators: { notEmpty: { message: "Last Name is required" } } },
                        email: { validators: { notEmpty: { message: "Email address is required" }, emailAddress: { message: "The value is not a valid email address" } } },
                        password: {
                            validators: {
                                notEmpty: { message: "The password is required" },
                                callback: {
                                    message: "Please enter valid password",
                                    callback: function (e) {
                                        if (e.value.length > 0) return 100 === r.getScore();
                                    },
                                },
                            },
                        },
                        "confirm-password": {
                            validators: {
                                notEmpty: { message: "The password confirmation is required" },
                                identical: {
                                    compare: function () {
                                        return e.querySelector('[name="password"]').value;
                                    },
                                    message: "The password and its confirm are not the same",
                                },
                            },
                        },
                        toc: { validators: { notEmpty: { message: "You must accept the terms and conditions" } } },
                    },
                    plugins: { trigger: new FormValidation.plugins.Trigger({ event: { password: !1 } }), bootstrap: new FormValidation.plugins.Bootstrap5({ rowSelector: ".fv-row", eleInvalidClass: "", eleValidClass: "" }) },
                })),
                t.addEventListener("click", function (r) {
                    r.preventDefault(),
                        a.revalidateField("password"),
                        a.validate().then(function (a) {
                            "Valid" == a
                                ? (t.setAttribute("data-kt-indicator", "on"),
                                  (t.disabled = !0),
                                  setTimeout(function () {
                                    //   var formData = new FormData(e);
                                    //   t.removeAttribute("data-kt-indicator"), (t.disabled = !1), e.submit();
                                    t.removeAttribute("data-kt-indicator"), (t.disabled = !1), 
                                         $.ajax({
                                          url: e.action,
                                        //   async : true,
                        					data :  {"last_name":e.querySelector('[name="last_name"]').value,"first_name":e.querySelector('[name="first_name"]').value,"email":e.querySelector('[name="email"]').value,"password":e.querySelector('[name="password"]').value },
                        				// data : formData ,
                        					type : 'post',
                        					statusCode : {
                        						500 : function() {
                        							toastr.error("<b>Server Error Contact To System Admin.</b>");
                        						}
                        					},
                                          success: function(responce){
                                        //   responce = JSON.parse(responce);
                                          if(responce.status==1)
                                          {
                                              Swal.fire({ text:  responce.message, icon: "success", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn btn-primary" } }).then(function (e1) {
                                              e1.isConfirmed && ((e.querySelector('[name="toc"]').click()),(e.querySelector('[name="last_name"]').value=""),(e.querySelector('[name="last_name"]').value=""),(e.querySelector('[name="last_name"]').value=""),(e.querySelector('[name="email"]').value = ""), (e.querySelector('[name="password"]').value = ""));
                                              window.location.href=responce.url;
                                          });
                                          
                                          }else{
                                            //   responce.message;
                                            Swal.fire({ text: responce.message, icon: "error", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn btn-primary" } }).then(function (e1) {
                                              e1.isConfirmed && ((e.querySelector('[name="toc"]').click()),(e.querySelector('[name="last_name"]').value=""),(e.querySelector('[name="last_name"]').value=""),(e.querySelector('[name="last_name"]').value=""),(e.querySelector('[name="email"]').value = ""), (e.querySelector('[name="password"]').value = ""));
                                              window.location.href=responce.url;
                                            });
                                          }
                                          }
                                        });
                                  },  2e3))
                                : Swal.fire({
                                      text: "Sorry, looks like there are some errors detected, please try again.",
                                      icon: "error",
                                      buttonsStyling: !1,
                                      confirmButtonText: "Ok, got it!",
                                      customClass: { confirmButton: "btn btn-primary" },
                                  });
                        });
                }),
                e.querySelector('input[name="password"]').addEventListener("input", function () {
                    this.value.length > 0 && a.updateFieldStatus("password", "NotValidated");
                });
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTSignupGeneral.init();
});
