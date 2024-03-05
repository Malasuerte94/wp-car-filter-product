jQuery(document).ready(function($) {
  const urlParams = new URLSearchParams(window.location.search);
  const carBrand = urlParams.get('car_brand');
  const carModel = urlParams.get('car_model');

  if(carBrand) {
    $('#car_brand_select').val(carBrand).trigger('change');
  }

  $('#car_brand_select').change(function() {
    let brandSlug = $(this).val(); // Get selected car brand slu
    if (brandSlug) {
      $.ajax({
        url: ccc_ajax_obj.ajaxurl,
        type: 'POST',
        data: {
          action: 'ccc_load_models',
          carBrand: brandSlug
        },
        success: function(response) {
          $('#car_model_select').html(response).prop('disabled', false);
        }
      });
    } else {
      $('#car_model_select').html('<option value="">Select Car Model</option>').prop('disabled', true);
    }
  });



  $('#car_model_select').change(function() {
    let brandSlug = $('#car_brand_select').val();
    let modelSlug = $(this).val();

    // AJAX request to filter products
    $.ajax({
      url: ccc_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'ccc_filter_products',
        carBrand: brandSlug,
        carModel: modelSlug
      },
      success: function(response) {
        $('.products').html(response);
      }
    });

    let newUrl = updateUrlParameter(window.location.href, 'car_brand', brandSlug);
    newUrl = updateUrlParameter(newUrl, 'car_model', modelSlug);
    window.history.pushState({path:newUrl}, '', newUrl);
  });


});


function updateUrlParameter(url, paramName, paramValue) {
  if (paramValue == null) paramValue = '';
  var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
  if (url.search(pattern)>=0) {
    return url.replace(pattern,'$1' + paramValue + '$2');
  }
  url = url.replace(/[?#]$/, '');
  return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
}
