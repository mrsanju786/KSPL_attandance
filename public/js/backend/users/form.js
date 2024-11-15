$(function () {
    // Change the menu nav
    var url = baseUrl + "/users/add"; // Change the url base on page
    if(typePage == 'edit'){
        $('ul.nav-sidebar').find('a.nav-link').filter(function() {
            return this.href == url;
        }).addClass('active');

        $('ul.nav-sidebar').find('a.nav-link').filter(function() {
            return this.href == url;
        }).parent().parent().parent().addClass('menu-open');

        $('ul.nav-sidebar').find('a.nav-link').filter(function() {
            return this.href == url;
        }).parent().parent().parent().find('a.nav-item').addClass('active');
    }

    // toggle password in plaintext if checkbox is selected
    $("#show-password").click(function () {
        $(this).is(":checked") ? $("#password").prop("type", "text") : $("#password").prop("type", "password");
    });

    // preview and validate for image, limit size 5MB
    var _URL = window.URL || window.webkitURL;
    $("input:file[name='image']").change(function (e) {
        e.preventDefault();
        $preview = $('#' + e.target.name + '_preview');
        var file, img, reader;
        var maxWidth = $(this).attr('data-max-width');
        var maxHeight = $(this).attr('data-max-height');

        // check if image file is selected or not in file selection dialog
        if (e.target.files[0]) {
            file = e.target.files[0],
                reader = new FileReader();

            // file size check
            if ((file.size / 1024) / 1024 > 5) {
                // over file size
                alert('The upper limit of files that can be attached is 5 MB.');
                cancelImage();
            } else {
                // check image width and height
                img = new Image();
                img.onload = function () {
                    var width  = img.naturalWidth  || img.width;
                    var height = img.naturalHeight || img.height;
                    console.log(width + ':' + height);

                    if (width > maxWidth || height > maxHeight)
                    {
                        alert('Please upload the image (Recommended size: 160px × 160px)');
                        cancelImage();
                    }
                };
                img.src = _URL.createObjectURL(file);

                // preview
                reader.onload = (function(file) {
                    return function(e) {
                        $preview.empty();
                        $preview.append($('<img>').attr({
                            src:   e.target.result,
                            width: '200px',
                            title: file.name,
                            class: 'img-circle elevation-2'
                        }));
                        $preview.next('p').addClass('show');
                    };
                }) (file);
                reader.readAsDataURL(file);
            }
        } else {
            // open file select model and not selected
            cancelImage();
        }

        // delete preview and value to empty
        function cancelImage() {
            $preview.empty();
            $('[name="' + e.target.name + '"]').val('');
            $preview.next('.delete-image-preview').removeClass('show');
            return false;
        }
    });
});

$('.select2').select2();

$('#multiple_area').hide();
$('#tsm_rsm_div').hide();
$('#rsm_div').hide();


$('#role').on('change',function(e){
    var role=$(this).val();
    if(role==5 || role==6){
        $('#multiple_area').show();
        $('#tsm_rsm_div').hide();
    }
    else if(role==3){
        $('#tsm_rsm_div').show();
        $('#multiple_area').hide();
        $('#rsm_div').hide();
    }
    else{
        $('#tsm_rsm_div').hide();
        $('#multiple_area').hide();
        $('#rsm_div').hide();
    }


    if(role==5){
        $('#rsm_div').show();
    }
    if(role==6){
        $('#rsm_div').hide();
    }
    
});

var role=$('#role').val();
if(role==5 || role==6){
    $('#multiple_area').show();
    $('#tsm_rsm_div').hide();
}
else if(role==3){
    $('#tsm_rsm_div').show();
    $('#multiple_area').hide();
    $('#rsm_div').hide();
}
else{
    $('#tsm_rsm_div').hide();
    $('#multiple_area').hide();
    $('#rsm_div').hide();
}


if(role==5){
    $('#rsm_div').show();
}

if(role==6){
    $('#rsm_div').hide();
}




function deleteImagePreview(element) {
    $(element).parent('.image-preview-area').prevAll('input').val('');
    // $(element).prev('div').html('');
    $('#image_preview img.img-circle').attr("src", baseUrl + "/img/default-user.png");
    $(element).next('input').val(1);
    $(element).removeClass('show');
}
