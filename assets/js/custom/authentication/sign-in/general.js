"use strict";
var KTSigninGeneral = (function () {
    var t, e, i;
    return {
        init: function () {
            (t = document.querySelector("#kt_sign_in_form")),
                (e = document.querySelector("#kt_sign_in_submit")),
                (i = FormValidation.formValidation(t, {
                    fields: {
                        email: { validators: { notEmpty: { message: "Email address is required" }, emailAddress: { message: "The value is not a valid email address" } } },
                        password: { validators: { notEmpty: { message: "The password is required" } } },
                    },
                    plugins: { trigger: new FormValidation.plugins.Trigger(), bootstrap: new FormValidation.plugins.Bootstrap5({ rowSelector: ".fv-row" }) },
                })),
                e.addEventListener("click", function (n) {
                    n.preventDefault(),
                        i.validate().then(function (i) {
                            "Valid" == i
                                ? (e.setAttribute("data-kt-indicator", "on"),
                                  (e.disabled = !0),
                                  setTimeout(function () {
                                      e.removeAttribute("data-kt-indicator"),
                                          (e.disabled = !1),
                                        //   function(){
                                        //   Swal.fire({ text: "You have successfully logged in!", icon: "success", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn btn-primary" } }).then(function (e) {
                                        //       e.isConfirmed && ((t.querySelector('[name="email"]').value = ""), (t.querySelector('[name="password"]').value = ""));
                                        //   });  
                                        // alert("CALL");
                                        // t.submit();
                                        
                                        $.ajax({
                                          url: t.action,
                                          async : true,
                        					data :  {"email":t.querySelector('[name="email"]').value,"password":t.querySelector('[name="password"]').value },
                        					type : 'post',
                        					statusCode : {
                        						500 : function() {
                        							toastr.error("<b>Server Error Contact To System Admin.</b>");
                        						}
                        					},
                                          success: function(responce){
                                            if(responce.status==1)
                                            {
                                                Swal.fire({ text:  responce.message, icon: "success", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn btn-primary" } }).then(function (e) {
                                                    e.isConfirmed && ((t.querySelector('[name="email"]').value = ""), (t.querySelector('[name="password"]').value = ""));
                                                    window.location.href=responce.url;
                                                });
                                            } else if(responce.status==2) {
                                                // Handle multiple roles
                                                const roleModalEl = document.getElementById('roleSelectionModal');
                                                if (roleModalEl) {
                                                    const roleModal = bootstrap.Modal.getOrCreateInstance(roleModalEl);
                                                    const roleList = document.getElementById('roleOptionsList');
                                                    if (roleList) {
                                                        roleList.innerHTML = '';
                                                        responce.roles.forEach(function(role) {
                                                            const btn = document.createElement('button');
                                                            btn.type = 'button';
                                                            btn.className = 'btn btn-outline btn-outline-dashed btn-outline-primary p-4 text-start login-role-btn mb-2';
                                                            btn.setAttribute('data-id', role.id);
                                                            btn.innerHTML = `<span class="d-block fw-bold fs-4">${role.designation}</span>`;
                                                            btn.onclick = function() {
                                                                const roleId = this.getAttribute('data-id');
                                                                const email = t.querySelector('[name="email"]').value;
                                                                const password = t.querySelector('[name="password"]').value;
                                                                roleModal.hide();
                                                                
                                                                // Final login step
                                                                 $.ajax({
                                                                     url: t.action.replace('accept_login', 'process_role_login'),
                                                                     type: 'POST',
                                                                     data: { email, password, user_id: roleId },
                                                                    success: function(res) {
                                                                        if (res.status == 1) {
                                                                            window.location.href = res.url;
                                                                        } else {
                                                                            Swal.fire({ text: res.message, icon: "error", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn btn-primary" } });
                                                                        }
                                                                    }
                                                                });
                                                            };
                                                            roleList.appendChild(btn);
                                                        });
                                                        roleModal.show();
                                                    }
                                                }
                                            } else {
                                                Swal.fire({ text: responce.message, icon: "error", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn btn-primary" } });
                                            }
                                          }
                                        });
                                        //   }
                                          
                                  }, 2e3))
                                : Swal.fire({
                                      text: "Sorry, looks like there are some errors detected, please try again.",
                                      icon: "error",
                                      buttonsStyling: !1,
                                      confirmButtonText: "Ok, got it!",
                                      customClass: { confirmButton: "btn btn-primary" },
                                  });
                        });
                });
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});
