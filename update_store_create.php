<?php
$file = file_get_contents('d:/kweek-admin-panel/resources/views/stores/create.blade.php');

$replacement = '            } else {
                if (story_vedios.length > 0 || story_thumbnail != "") {
                    if (story_vedios.length > 0 && story_thumbnail == "") {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans(\'lang.story_error\') }}</p>");
                        window.scrollTo(0, 0);
                        return false;
                    } else if (story_thumbnail && story_vedios.length == 0) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans(\'lang.story_error\') }}</p>");
                        window.scrollTo(0, 0);
                        return false;
                    }
                }
                jQuery("#data-table_processing").show();
                var formData = new FormData();
                formData.append("_token", "{{ csrf_token() }}");
                formData.append("title", vendorname);
                formData.append("description", description);
                formData.append("latitude", latitude);
                formData.append("longitude", longitude);
                formData.append("location", address);
                formData.append("categoryID", cuisines);
                formData.append("phonenumber", country_code + phonenumber);
                formData.append("categoryTitle", categoryTitle);
                formData.append("filters", JSON.stringify(filters_new));
                formData.append("authorName", name);
                formData.append("enabledDiveInFuture", enabledDiveInFuture);
                formData.append("specialDiscountEnable", enabledSpecialOffer);
                formData.append("vendorCost", vendorCost);
                formData.append("openDineTime", openDineTime);
                formData.append("closeDineTime", closeDineTime);
                formData.append("workingHours", JSON.stringify(workingHours));
                formData.append("specialDiscount", JSON.stringify(specialDiscount));
                formData.append("subscription_plan", subscription_plan);
                formData.append("subscriptionPlanId", subscriptionPlanId);
                formData.append("subscriptionExpiryDate", subscriptionExpiryDate);
                formData.append("subscriptionTotalOrders", subscriptionOrderLimit);
                formData.append("adminCommission", adminCommission);
                formData.append("isSelfDelivery", enable_self_delivery);
                formData.append("zoneId", zoneId);
                formData.append("section_id", section_id);
                formData.append("delivery_charges_per_km", delivery_charges_per_km);
                formData.append("minimum_delivery_charges", minimum_delivery_charges);
                formData.append("minimum_delivery_charges_within_km", minimum_delivery_charges_within_km);

                formData.append("user_name", userFirstName);
                formData.append("user_last_name", userLastName);
                formData.append("user_email", email);
                formData.append("user_password", password);
                formData.append("user_phone", userPhone);
                
                if (document.getElementById("vendor_image") && document.getElementById("vendor_image").files[0]) formData.append("photo", document.getElementById("vendor_image").files[0]);
                if (document.getElementById("owner_image") && document.getElementById("owner_image").files[0]) formData.append("authorProfilePic", document.getElementById("owner_image").files[0]);
                if (document.getElementById("story_thumbnail") && document.getElementById("story_thumbnail").files[0]) formData.append("storyThumbnail", document.getElementById("story_thumbnail").files[0]);
                
                if(document.getElementById("gallery_image")) {
                    var files_gallery = document.getElementById("gallery_image").files;
                    for(var i = 0; i < files_gallery.length; i++) { formData.append("photos[]", files_gallery[i]); }
                }

                if(document.getElementById("vendor_menu_photos")) {
                    var files_menu = document.getElementById("vendor_menu_photos").files;
                    for(var i = 0; i < files_menu.length; i++) { formData.append("vendorMenuPhotos[]", files_menu[i]); }
                }
                
                if(document.getElementById("story_video")) {
                    var files_video = document.getElementById("story_video").files;
                    for(var i = 0; i < files_video.length; i++) { formData.append("storyVideo[]", files_video[i]); }
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route(\'stores.store\') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        jQuery("#data-table_processing").hide();
                        window.location.href = "{{ route(\'stores\') }}";
                    },
                    error: function (xhr) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText) + "</p>");
                        window.scrollTo(0, 0);
                    }
                });
            }';

$new_file = preg_replace('/\} else \{\s+if \(story_vedios\.length > 0 \|\| story_thumbnail != \'\'\) \{.*?\$\("\.add_special_offer_restaurant_btn"\)\.click/s', $replacement . "\n        });\n\n        \$(\".add_special_offer_restaurant_btn\").click", $file);
file_put_contents('d:/kweek-admin-panel/resources/views/stores/create.blade.php', $new_file);
echo "Done create\n";
