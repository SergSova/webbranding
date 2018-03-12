$(function () {
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
                        $('.geo-lems').html(' ');
                        objPArse('lems', '.lems');
                        objPArse('geo', '.geo-lems');
                        console.log('end');
                        is_stop = true;
                        return;
                    }
                    var message = $('.message-wrap');
                    offset += limit;
                    message.find('.message').removeClass('clear').removeClass('error').addClass('success').html(offset + ' from ' + textCount);
                    message.html(message.html());
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
        is_stop = true;
        $('.work-btn').removeAttr('disabled');
    });
    $('.work-btn').on('click', function (e) {
        is_stop = false;
        e.preventDefault(e);
        sendReq();
    });
    $('.clear-btn').on('click', function (e) {
        e.preventDefault(e);
        is_stop = true;
        $('.work-btn').removeAttr('disabled');
        localStorage.clear();
        sessionStorage.clear();
        $.ajax({
            method: 'post',
            data: {working: "Clear"},
            success: function () {
                $('.lems').html(' ');
                $('.geo-lems').html(' ');
                $('.message').removeClass('error').removeClass('success').addClass('clear').html('All Cleared');
            }
        });
    });
    $('.geo_reg_input').on('change', function () {
        var elem = $(this);
        if (elem.is(":checked") || this.indeterminate) {
            elem.siblings('.region-wrap').children('.geo_input').each(function () {
                $(this).attr('checked', 'checked')
            });
        } else
            elem.siblings('.region-wrap').children('.geo_input').each(function () {
                $(this).removeAttr('checked');
            });
    });

    $('.geo_input').on('change', function () {
        var $this = $(this);
        var _that = this;
        $(this).parent().siblings('.geo_reg_input').each(function () {
            var allChecked = $this.siblings('.geo_input:checked').length;
            if (_that.checked) {
                allChecked++;
            }
            var allInput = $this.parent().find('.geo_input').length;
            this.indeterminate = allChecked < allInput && allChecked != 0;
            this.checked = allChecked == allInput;
        })
    });

    $('.geo_slect').on('change', function () {
        var elem = $(this);
        if (!elem.is(":checked")) {
            $('.geo-wrap input').each(function () {
                // this.checked = false;
                this.disabled = true;
            });
        } else {
            $('.geo-wrap input').each(function () {
                this.disabled = false;
            });
        }
    });

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
        if (!confirm('Добавить слово ' + lem + ' в исключение')) {
            return;
        }
        if (checkIdentical(lem)) {
            return;
        }
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
        var geo_lems = $('.geo-wrap');
        var lem = $(this).siblings('.lemma').data('lem');
        if (!confirm('Переместить слово ' + lem + ' в гео данные')) {
            return;
        }
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
                div.appendTo(geo_lems);

                var geo = $this.parent().clone();
                geo.find('.lemma-lems').removeClass('lemma-lems').addClass('lemma-geo').data('obj', 'geo');
                geo.find('.to-geo').remove();
                geo.appendTo('.geo-lems');
                $this.parent().fadeOut(0);
            }
        });
    }
})
;