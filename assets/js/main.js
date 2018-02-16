$(function () {
    function get_resp(resp) {
        // console.log(resp);
        console.log($(resp).find('.var-dump').html());
        obj = $(resp);

        $('.lems-wrap').html(obj.find('.lems-wrap').html());
        $('.texts').html(obj.find('.texts').html());

        $('.lems-wrap .add-exception').bind('click', addException);
        $('.except-rem a').bind('click', delExcept);
        $('.geo-rem a').bind('click', delGeo);
        $('.texts .del-text').bind('click', delText);
        $('.lem-text .klem').bind('click', klemClick);
    }

    function ajaxReq(data) {
        $.ajax({
            method: 'post',
            data: data,
            success: get_resp
        });
    }

    function klemClick(e) {
        e.preventDefault(e);
        var _this = $(this);
        var data = {'klem': _this.data('value'), 'working': 'Work'};
        ajaxReq(data);
    }

    function addException(e) {
        e.preventDefault(e);
        var _this = $(this);
        var data = {'exception': _this.data('exception'), 'working': 'Work'};
        ajaxReq(data);
    }

    function delText(e) {
        e.preventDefault(e);
        var _this = $(this);
        var data = {'text_del_id': _this.data('id'), 'working': 'Clear'};
        ajaxReq(data);
    }

    function delGeo(e) {
        e.preventDefault(e);
        var _this = $(this);
        var data = {'geo_del_id': _this.data('id'), 'working': 'Clear'};
        ajaxReq(data);
    }

    function delExcept(e) {
        e.preventDefault(e);
        var _this = $(this);
        var data = {'except_del_id': _this.data('id'), 'working': 'Clear'};
        ajaxReq(data);
    }

    function addGeo(e) {
        e.preventDefault(e);
        var data = {'geoName': $(this).data('geo'), 'working': 'ChangeToGeo'};
        ajaxReq(data);
    }

    $('.lems-wrap .add-exception').bind('click', addException);
    $('.except-rem a').bind('click', delExcept);
    $('.geo-rem a').bind('click', delGeo);
    $('.texts .del-text').bind('click', delText);
    $('.lem-text .klem').bind('click', klemClick);

    $('.add-geo').bind('click', addGeo);


    var limit = 100;
    var offset = 0;
    var is_stop = false;
    var k;

    function sendReq() {
        if (!is_stop) {
            var is_last = offset >= textCount;
            var data = $('.filter-wrap form').serializeArray();
            data.push({name: "limit", value: limit});
            data.push({name: "offset", value: offset});
            data.push({name: "working", value: 'Work'});
            data.push({name: "is_last", value: is_last});

            $.ajax({
                type: "post",
                data: data,
                success: function (resp) {
                    if (is_last) {
                        k = JSON.parse(resp);
                        objPArse('lems', '.lems');
                        objPArse('geo', '.geo-lems');
                        console.log('end');
                        is_stop = true;
                        return;
                    }
                    var message = $(resp).find('.message-wrap ');
                    offset += limit;
                    message.find('.message').html(message.find('.message').html() + ' ' + offset + ' from ' + textCount);
                    $('.message-wrap').html(message.html());
                },
                complete: sendReq
            });

        }
    }

    function objPArse(z, obj) {
        for (key in k[z]) {
            $(obj).append('<div>\n    <span class="lemma-' + z + '" data-obj="' + z + '">' + key + '(' + k[z][key]['count'] + ')</span> <span class="excl">exc</span>/<span class="to-geo">geo</span>\n</div>')
        }
        $('.lemma-' + z).click(function () {
            if ($(this).find('.sentence').length) {
                $(this).find('.sentence').remove();
                return;
            }
            arr = $(this).attr('data-obj');
            word = $(this).html().split('(');
            words = '';

            words = k[z][word[0]].text.join(', ');
            $(this).append('<div class="sentence">' + words + '</div>')
        });

        var excl = $('.excl');
        excl.unbind('click');
        excl.bind('click', function () {

        });
        var geo = $('.to-geo');
        geo.unbind('click');
        geo.bind('click', function () {

        });

    }

    $('.stop-btn').on('click', function () {
        is_stop = true;
    });
    $('.work-btn').on('click', function (e) {
        is_stop = false;
        e.preventDefault(e);
        sendReq();
    });

    $('.excluded_input').on('change', function () {
        var data = $('.filter-wrap form').serialize();
        $.ajax({
            method: "POST",
            data: data || {excluded_words: '#'},
            success: function (resp) {
                console.log(resp);
            }
        });
    });
    $('.geo_reg_input,.geo_input').on('change', function () {
        var data = $('.filter-wrap form').serialize();
        $.ajax({
            method: "POST",
            data: data || {include_geo: '#'},
            success: function (resp) {
                console.log(resp);
            }
        });
    });

    $('.geo_slect').on('change', function () {
        if (!$(this).is(':checked')) {
            $.ajax({
                method: "POST",
                data: {is_geo_data: '#'}
            });
        }
    });
});