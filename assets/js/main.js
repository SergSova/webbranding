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

            $('.work-btn').attr('disabled', 'disabled');
            $.ajax({
                type: "post",
                data: data,
                success: function (resp) {
                    if (is_last) {
                        offset = 0;
                        k = JSON.parse(resp);
                        $('.lems').html(' ');
                        $('.geo-lems').html('');
                        objPArse('lems', '.lems');
                        objPArse('geo', '.geo-lems');
                        console.log('end');
                        is_stop = true;
                        return;
                    }
                    var message = $('.message-wrap');
                    offset += limit;
                    message.find('.message').html(offset + ' from ' + textCount);
                    $('.message-wrap').html(message.html());
                },
                complete: sendReq
            });

        }
    }

    function objPArse(z, obj) {
        for (key in k[z]) {
            var div = $('<div/>');
            var lema = $('<span class="lemma" />')
                .addClass('lemma-' + z)
                .data('obj', z)
                .data('lem', k[z][key]['word'])
                .text(k[z][key]['word'] + ' (' + k[z][key]['count'] + ')');
            var excl_span = $('<span class="excl">exc</span>');
            var geo_span = $('<span class="to-geo">geo</span>');
            div.append(lema);
            div.append(excl_span);
            if (z != 'geo') {
                div.append('/');
                div.append(geo_span);
            }
            $(obj).append(div);

        }
        $('.lemma-' + z).click(function () {
            if ($(this).find('.sentence').length) {
                $(this).find('.sentence').remove();
                return;
            }
            arr = $(this).attr('data-obj');
            word = $(this).html().split('(');
            words = '';

            words = k[z][$.trim(word[0])].text.join(', ');
            $(this).append('<div class="sentence">' + words + '</div>')
        });

        var excl = $('.excl');
        excl.unbind('click');
        excl.bind('click', exclClick);

        var geo = $('.to-geo');
        geo.unbind('click');
        geo.bind('click', to_geoClick);

    }

    $('.stop-btn').on('click', function () {
        $('.work-btn').removeAttr('disabled');
        is_stop = true;
    });
    $('.work-btn').on('click', function (e) {
        is_stop = false;
        e.preventDefault(e);
        sendReq();
    });

    /*    $('.excluded_input').on('change', function () {
            var data = $('.filter-wrap form').serialize();
            $.ajax({
                method: "POST",
                data: data || {excluded_words: '#'},
                success: function (resp) {
                    // console.log(resp);
                }
            });
        });*/

    /*$('.geo_reg_input,.geo_input').on('change', function () {
        var data = $('.filter-wrap form').serializeArray();
        data.push({name: "working", value: 'filter'});
        $.ajax({
            method: "POST",
            data: data || {working: 'filter', include_geo: '#'},
            success: function (resp) {
                console.log(resp);
            }
        });
    });*/

    /*$('.geo_slect').on('change', function () {
        if (!$(this).is(':checked')) {
            $.ajax({
                method: "POST",
                data: {is_geo_data: '#'}
            });
        }
    });*/
    function checkIdentical(word) {
        var p = false;

        $('.excluded-rem div label').each(function () {
            var w = $(this).html();
            if (w == word) {
                p = true;
            }
        });
        return p;
    }

    function exclClick() {
        var lem = $(this).siblings('.lemma').data('lem');
        if (checkIdentical(lem)) return;
        var excluded = $('.excluded-rem');
        var ex_count = excluded.find('input').length;
        ex_count++;
        var input = $('<input type="checkbox" />')
            .addClass('excluded_input')
            .attr('id', 'ex_' + ex_count)
            .attr('name', 'excluded_words' + ex_count)
            .attr('checked', 'checked');
        var label = $('<label />').attr('for', 'ex_' + ex_count).html(lem);
        var div = $('<div/>');

        input.appendTo(div);
        label.appendTo(div);
        div.appendTo(excluded);

        $(this).parent().fadeOut(0);

        $.ajax({
            method: "POST",
            data: {working: 'addExc', word: lem},
            success: function (resp) {
                console.log(resp);
            }
        });
    }

    function to_geoClick() {
        var excluded = $('.geo-wrap');
        var lem = $(this).siblings('.lemma').data('lem');
        var $this = $(this);
        $.ajax({
            method: "POST",
            data: {working: 'addGeo', word: lem},
            success: function (geo_id) {
                var input = $('<input type="checkbox" />')
                    .addClass('geo_input')
                    .attr('id', 'geo_' + geo_id)
                    .attr('name', 'include_geo' + geo_id)
                    .attr('checked', 'checked');
                var label = $('<label />').attr('for', 'geo_' + geo_id).html(lem);
                var div = $('<div/>');

                input.appendTo(div);
                label.appendTo(div);
                div.appendTo(excluded);

                var geo = $this.parent().clone();
                geo.find('.lemma-lems').removeClass('lemma-lems').addClass('lemma-geo').data('obj', 'geo');
                geo.appendTo('.geo-lems');
                $this.parent().fadeOut(0);

            }
        });


    }

});