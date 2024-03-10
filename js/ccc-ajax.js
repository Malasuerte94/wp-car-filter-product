jQuery(document).ready(function($) {

  const urlParams = new URLSearchParams(window.location.search);
  const carBrand = urlParams.get('car_brand');
  const carModel = urlParams.get('car_model');
  const carBrandField = $('.car_brand_select');
  const carModelField = $('#car_model_select');

  if(carBrand) {
    carBrandField.val(carBrand).trigger('change');
  }

  carBrandField.change(function() {
    let brandSlug = $(this).val();
    let modelSlug = carModelField.val();

    if (brandSlug) {
      $.ajax({
        url: ccc_ajax_obj.ajaxurl,
        type: 'GET',
        data: {
          action: 'ccc_load_models',
          carBrand: brandSlug
        },
        success: function(response) {
          carModelField.html(response).prop('disabled', false);
        }
      });
    } else {
      carModelField.html('<option value="">Select Car Model</option>').prop('disabled', true);
    }
    updateUrlParam(brandSlug, modelSlug);
  });


  carModelField.change(function() {
    let brandSlug = carBrandField.val();
    let modelSlug = $(this).val();

    $.ajax({
      url: ccc_ajax_obj.ajaxurl,
      type: 'GET',
      data: {
        action: 'ccc_filter_products',
        carBrand: brandSlug,
        carModel: modelSlug
      },
      success: function(response) {
        $('.products').html(response);
      }
    });
    updateUrlParam(brandSlug, modelSlug);
  });


  const carBrandSelects = document.querySelectorAll('.car_brand_select');

  carBrandSelects.forEach(function(select) {
    select.addEventListener('change', function() {
      document.querySelectorAll('.car-model label').forEach(function(label) {
        label.classList.remove('active');
      });
      const label = this.parentElement;
      label.classList.add('active');
    });
  });

});


function updateUrlParam(brandSlug, modelSlug) {
  let newUrl = updateUrlParameter(window.location.href, 'car_brand', brandSlug);
  newUrl = updateUrlParameter(newUrl, 'car_model', modelSlug);
  window.history.pushState({path:newUrl}, '', newUrl);
}

function updateUrlParameter(url, paramName, paramValue) {
  if (paramValue == null) paramValue = '';
  var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
  if (url.search(pattern)>=0) {
    return url.replace(pattern,'$1' + paramValue + '$2');
  }
  url = url.replace(/[?#]$/, '');
  return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
}
