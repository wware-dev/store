function mjvPostcodeGetXmlHttpRequest() {
    if (XMLHttpRequest) {
        return new XMLHttpRequest();
    } else {
        try {
            return new ActiveXObject('MSXML2.XMLHTTP.6.0');
        } catch (e) {
            try {
                return new ActiveXObject('MSXML2.XMLHTTP.3.0');
            } catch (e) {
                try {
                    return new ActiveXObject('MSXML2.XMLHTTP');
                } catch (e) {
                }
            }
        }
    }
    return null;
}

function mjvPostcodeGetZipInfo() {
    var xhr = mjvPostcodeGetXmlHttpRequest();
    if (xhr) {
        xhr.open("GET", "/magento/ecgjppostcode/code7/index/code7/" + this.value + "/id/" + this.id);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var obj = eval("(" + xhr.responseText + ")");
                if (obj.postcode_id > 0) {
                    if (obj.tag_id == 'zip') {
                        $('region_id').value = obj.region_id;
                        $('city').value = obj.city_kanji + obj.street_kanji;
                        $('street_1').focus();
                        $(obj.tag_id).value = obj.code7;
                    } else if (obj.prefix) {
                        $(obj.prefix + 'region_id').value = obj.region_id;
                        $(obj.prefix + 'city').value = obj.city_kanji + obj.street_kanji;
                        if ($(obj.prefix + 'street0')) {
                            $(obj.prefix + 'street0').focus();
                        } else {
                            $(obj.prefix + 'street1').focus();
                        }
                        $(obj.tag_id).value = obj.code7;
                    }
                }
            }
        }
        xhr.send(null);
    }
}

function mjvPostcodeIsTarget(tag) {
    if (tag.id == 'zip') {
        return true;
    }
    if (tag.id.length > 8) {
        if (tag.id.substr(tag.id.length - 8, 8) == 'postcode') {
            return true;
        }
    }
    return false;
}

function mjvPostcodeOnLoad() {
    var el = document.getElementsByTagName('input');
    for (var i = 0; i < el.length; i++) {
        if (mjvPostcodeIsTarget(el[i])) {
            if (el[i].addEventListener) {
                el[i].addEventListener('blur', mjvPostcodeGetZipInfo, false);
            } else if(el[i].attachEvent) {
                el[i].attachEvent('onblur', mjvPostcodeGetZipInfo);
                if (!el[i].onblur) {
                    el[i].onblur = mjvPostcodeGetZipInfo;
                }
            }
        }
    }
}

if (window.addEventListener) {
    window.addEventListener("load", mjvPostcodeOnLoad, false);
} else if (window.attachEvent) {
    window.attachEvent("onload", mjvPostcodeOnLoad);
}
