<?php
$file = file_get_contents('d:/kweek-admin-panel/resources/views/stores/edit.blade.php');

$replacement = '                } else {
                    jQuery("#data-table_processing").show();
                    var formData = new FormData();
                    formData.append("_token", "{{ csrf_token() }}");
                    formData.append("title", vendorname);
                    formData.append("description", description);
                    formData.append("latitude", latitude);
                    formData.append("longitude", longitude);
                    formData.append("location", address);
                    formData.append("categoryID", cuisines);
                    formData.append("phonenumber", phonenumber);
                    formData.append("categoryTitle", categoryTitle);
                    formData.append("filters", JSON.stringify(filters_new));
                    formData.append("enabledDiveInFuture", enabledDiveInFuture);
                    formData.append("specialDiscountEnable", enabledSpecialOffer);
                    formData.append("vendorCost", vendorCost);
                    formData.append("openDineTime", openDineTime);
                    formData.append("closeDineTime", closeDineTime);
                    formData.append("workingHours", JSON.stringify(workingHours));
                    formData.append("specialDiscount", JSON.stringify(specialDiscount));
                    formData.append("adminCommission", adminCommission);
                    formData.append("isSelfDelivery", enable_self_delivery);
                    formData.append("zoneId", zoneId);
                    formData.append("section_id", section_id);
                    formData.append("delivery_charges_per_km", delivery_charges_per_km);
                    formData.append("minimum_delivery_charges", minimum_delivery_charges);
                    formData.append("minimum_delivery_charges_within_km", minimum_delivery_charges_within_km);

                    if (typeof change_expiry_date !== "undefined" && change_expiry_date != null) formData.append("subscriptionExpiryDate", change_expiry_date);

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
                        url: "/stores/update/" + id,
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
                }
            })';

$new_file = preg_replace('/\} else \{\s+jQuery\("#data-table_processing"\)\.show\(\);\s+coordinates = new kweekDb\.GeoPoint.*?\}\)\.catch\(err => \{\s+jQuery\("#data-table_processing"\)\.hide\(\).*?\}\);\s+\}\s+\}\)/s', $replacement, $file);
if ($new_file && $new_file !== $file) {
    file_put_contents('d:/kweek-admin-panel/resources/views/stores/edit.blade.php', $new_file);
    echo "Done edit replacement\n";
} else {
    echo "Edit Regex failed\n";
}
