var REGEXP_NUMERIC = /^\d+$/,
    REGEXP_CENA = /^[\d]+(.[\d]{1,2})?$/,
    URL = 'http://' + DOMAIN + '/index.php?' + VALUES,
    AJAX_MAIN = 'http://' + DOMAIN + '/ajax/main.php?' + VALUES,
    setCookie = function(name, value) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + 1);
        document.cookie = name + '=' + value + '; path=/; expires=' + exdate.toGMTString();
    },
    getCookie = function(name) {
        var arr1 = document.cookie.split(name);
        if(arr1.length > 1) {
            var arr2 = arr1[1].split(/;/);
            var arr3 = arr2[0].split(/=/);
            return arr3[0] ? arr3[0] : arr3[1];
        } else
            return null;
    },
    delCookie = function(name) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate()-1);
        document.cookie = name + '=; path=/; expires=' + exdate.toGMTString();
    },
    sortable = function() {
        $('._sort').sortable({
            axis:'y',
            update:function () {
                var dds = $(this).find('dd'),
                    arr = [];
                for(var n = 0; n < dds.length; n++)
                    arr.push(dds.eq(n).attr('val'));
                var send = {
                    op:'sort',
                    table:$(this).attr('val'),
                    ids:arr.join()
                };
                $('#mainLinks').addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    $('#mainLinks').removeClass('busy');
                }, 'json');
            }
        });
    },
    _end = function(count, arr) {
        if(arr.length == 2)
            arr.push(arr[1]);
        var send = arr[2];
        if(Math.floor(count / 10 % 10) != 1)
            switch(count % 10) {
                case 1: send = arr[0]; break;
                case 2: send = arr[1]; break;
                case 3: send = arr[1]; break;
                case 4: send = arr[1]; break;
            }
        return send;
    },
    hashLoc,
    hashSet = function(hash) {
        if(!hash && !hash.p)
            return;
        hashLoc = hash.p;
        var s = true;
        switch(hash.p) {
            case 'client':
                if(hash.d == 'info')
                    hashLoc += '_' + hash.id;
                break;
            case 'zayav':
                if(hash.d == 'info')
                    hashLoc += '_' + hash.id;
                else if(hash.d == 'add')
                    hashLoc += '_add' + (REGEXP_NUMERIC.test(hash.id) ? '_' + hash.id : '');
                else if(!hash.d)
                    s = false;
                break;
            default:
                if(hash.d) {
                    hashLoc += '_' + hash.d;
                    if(hash.d1)
                        hashLoc += '_' + hash.d1;
                }
        }
        if(s)
            VK.callMethod('setLocation', hashLoc);
    },
    clientAdd = function(callback) {
        var html = '<table style="border-spacing:10px">' +
                '<tr><td class="label">Имя:<TD><input type="text" id="fio" style="width:220px;">' +
                '<tr><td class="label">Телефон:<TD><input type="text" id="telefon" style=width:220px;>' +
                '<tr><td class="label">Адрес:<TD><input type="text" id="adres" style=width:220px;>' +
                '</TABLE>',
            dialog = _dialog({
                width:340,
                head:'Добавление нoвого клиента',
                content:html,
                submit:submit
            });
        $('#fio').focus();
        $('#fio,#telefon,#adres').keyEnter(submit);
        function submit() {
            var send = {
                op:'client_add',
                fio:$('#fio').val(),
                telefon:$('#telefon').val(),
                adres:$('#adres').val()
            };
            if(!send.fio) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">Не указано имя клиента.</SPAN>',
                    top:-47,
                    left:81,
                    indent:40,
                    show:1,
                    remove:1,
                    correct:0
                });
                $('#fio').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        _msg('Новый клиент внесён.');
                        if(typeof callback == 'function')
                            callback(res);
                        else
                            document.location.href = URL + '&p=client&d=info&id=' + res.uid;
                    } else dialog.abort();
                }, 'json');
            }
        }
    },
    clientFilter = function() {
        var v = {
            fast:cFind.inp(),
            dolg:$('#dolg').val(),
            active:$('#active').val()
        };
        $('.filter')[v.fast ? 'hide' : 'show']();
        return v;
    },
    clientSpisokLoad = function() {
        var send = clientFilter(),
            result = $('.result');
        send.op = 'client_spisok_load';
        if(result.hasClass('busy'))
            return;
        result.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            result.removeClass('busy');
            if(res.success) {
                result.html(res.all);
                $('.left').html(res.spisok);
            }
        }, 'json');
    };

$.fn.clientSel = function(obj) {
    var t = $(this);
    obj = $.extend({
        width:240,
        add:null,
        client_id:t.val() || 0
    }, obj);

    if(obj.add)
        obj.add = function() {
            clientAdd(function(res) {
                sel.add(res).val(res.uid)
            });
        };

    var sel = t.vkSel({
        width:obj.width,
        title0:'Начните вводить данные клиента...',
        spisok:[],
        ro:0,
        nofind:'Клиентов не найдено',
        funcAdd:obj.add,
        funcKeyup:clientsGet
    }).o;
    sel.process();
    clientsGet();

    function clientsGet(val) {
        var send = {
            op:'client_sel',
            val:val ? val : '',
            client_id:obj.client_id
        };
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                sel.spisok(res.spisok);
                if(obj.client_id > 0) {
                    sel.val(obj.client_id)
                    obj.client_id = 0;
                }
            }
        }, 'json');
    }
    return t;
};

$(document)
    .ajaxError(function(event, request, settings) {
        if(!request.responseText)
            return;
        alert('Ошибка:\n\n' + request.responseText);
    })
    .on('click', '#cache_clear', function() {
        $.post(AJAX_MAIN, {'op':'cache_clear'}, function(res) {
            if(res.success) {
                _msg('Кэш очищен.');
                document.location.reload();
            }
        }, 'json');
    })
    .on('click', '.debug_toggle', function() {
        var d = getCookie('debug');
        setCookie('debug', d == 0 ? 1 : 0);
        _msg('Debug включен.');
        document.location.reload();
    })

    .on('click', '#setup_product .add', function() {
        var t = $(this),
            html = '<table style="border-spacing:10px">' +
                '<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" style="width:250px" />' +
                '</table>',
            dialog = _dialog({
                top:60,
                width:390,
                head:'Добавление нового наименования изделия',
                content:html,
                submit:submit
            });
        $('#name').focus().keyEnter(submit);
        function submit() {
            var send = {
                op:'setup_product_add',
                name:$('#name').val()
            };
            if(!send.name) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>Не указано наименование</SPAN>',
                    top:-47,
                    left:131,
                    indent:50,
                    show:1,
                    remove:1
                });
                $('#name').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        $('.spisok').html(res.html);
                        dialog.close();
                        _msg('Внесено!');
                        sortable();
                    } else
                        dialog.abort();
                }, 'json');
            }
        }
    })
    .on('click', '#setup_product .img_edit', function() {
        var t = $(this);
        while(t[0].tagName != 'DD')
            t = t.parent();
        var id = t.attr('val'),
            name = t.find('.name').html(),
            html = '<table style="border-spacing:10px">' +
                '<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" style="width:250px" value="' + name + '" />' +
                '</table>',
            dialog = _dialog({
                top:60,
                width:390,
                head:'Редактирование наименования изделия',
                content:html,
                butSubmit:'Сохранить',
                submit:submit
            });
        $('#name').focus().keyEnter(submit);
        function submit() {
            var send = {
                op:'setup_product_edit',
                id:id,
                name:$('#name').val()
            };
            if(!send.name) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>Не указано наименование</SPAN>',
                    top:-47,
                    left:131,
                    indent:50,
                    show:1,
                    remove:1
                });
                $('#name').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        $('.spisok').html(res.html);
                        dialog.close();
                        _msg('Внесено!');
                        sortable();
                    } else
                        dialog.abort();
                }, 'json');
            }
        }
    })
    .on('click', '#setup_product .img_del', function() {
        var t = $(this),
            dialog = _dialog({
                top:90,
                width:300,
                head:'Удаление изделия',
                content:'<center><b>Подтвердите удаление изделия.</b></center>',
                butSubmit:'Удалить',
                submit:submit
            });
        function submit() {
            while(t[0].tagName != 'DD')
                t = t.parent();
            var send = {
                op:'setup_product_del',
                id:t.attr('val')
            };
            dialog.process();
            $.post(AJAX_MAIN, send, function(res) {
                if(res.success) {
                    $('.spisok').html(res.html);
                    dialog.close();
                    _msg('Удалено!');
                    sortable();
                } else
                    dialog.abort();
            }, 'json');
        }
    });

$(document).ready(function() {
    frameHidden.onresize = _fbhs;

    VK.callMethod('scrollWindow', 0);
    VK.callMethod('scrollSubscribe');
    VK.addCallback('onScroll', function(top) { VK_SCROLL = top; });

    sortable();
    _fbhs();

    if($('#client').length > 0) {
        window.cFind = $('#find')._search({
            width:602,
            focus:1,
            enter:1,
            txt:'Начните вводить данные клиента',
            func:clientSpisokLoad
        });
        $('#buttonCreate').vkHint({
            msg:'<B>Внесение нового клиента в базу.</B><br /><br />' +
                'После внесения Вы попадаете на страницу с информацией о клиенте для дальнейших действий.<br /><br />' +
                'Клиентов также можно добавлять при <A href="' + URL + '&p=zayav&d=add&back=client">создании новой заявки</A>.',
            ugol:'right',
            width:215,
            top:-38,
            left:-250,
            indent:40,
            delayShow:1000,
            correct:0
        }).click(clientAdd);
        $('#dolg_check').vkHint({
            msg:'<b>Список должников.</b><br /><br />' +
                'Выводятся клиенты, у которых баланс менее 0. Также в результате отображается общая сумма долга.',
            ugol:'right',
            width:150,
            top:-6,
            left:-185,
            indent:20,
            delayShow:1000,
            correct:0
        });
    }
    if($('#clientInfo').length > 0) {
        $('#dopLinks .link').click(function() {
            $('#dopLinks .link').removeClass('sel');
            $(this).addClass('sel');
            var val = $(this).attr('val');
            $('.res').css('display', val == 'zayav' ? 'block' : 'none');
            $('#zayav_filter').css('display', val == 'zayav' ? 'block' : 'none');
            $('#zayav_spisok').css('display', val == 'zayav' ? 'block' : 'none');
            $('#money_spisok').css('display', val == 'money' ? 'block' : 'none');
            $('#remind_spisok').css('display', val == 'remind' ? 'block' : 'none');
            $('#comments').css('display', val == 'comm' ? 'block' : 'none');
        });
        $('.cedit').click(function() {
            var html = '<TABLE class="client_edit">' +
                '<tr><td class="label">Имя:<TD><input type="text" id="fio" value="' + $('.fio').html() + '">' +
                '<tr><td class="label">Телефон:<TD><input type="text" id="telefon" value="' + $('.telefon').html() + '">' +
                '<tr><td class="label">Адрес:<TD><input type="text" id="adres" value="' + $('.adres').html() + '">' +
                '</TABLE>';
            var dialog = _dialog({
                head:'Редактирование данных клиента',
                top:60,
                width:380,
                content:html,
                butSubmit:'Сохранить',
                submit:submit
            });
            $('#fio,#telefon,#adres').keyEnter(submit);
            function submit() {
                var msg,
                    send = {
                        op:'client_edit',
                        client_id:CLIENT.id,
                        fio:$.trim($('#fio').val()),
                        telefon:$.trim($('#telefon').val()),
                        adres:$.trim($('#adres').val())
                    };
                if(!send.fio) {
                    msg = 'Не указано имя клиента.';
                    $("#fio").focus();
                } else {
                    dialog.process();
                    $.post(AJAX_MAIN, send, function(res) {
                        if(res.success) {
                            $('.fio').html(send.fio);
                            $('.telefon').html(send.telefon);
                            $('.adres').html(send.adres);
                            dialog.close();
                            _msg('Данные клиента изменены.');
                        } else
                            dialog.abort();
                    }, 'json');
                }
                if(msg)
                    dialog.bottom.vkHint({
                        msg:'<SPAN class=red>' + msg + '</SPAN>',
                        top:-47,
                        left:100,
                        indent:50,
                        show:1,
                        remove:1
                    });
            }
        });
        $('.cdel').click(function() {
            var dialog = _dialog({
                top:90,
                width:300,
                head:'Удаление клиента',
                content:'<center>Внимание!<br />Будут удалены все данные о клиенте,<br />его заявки, платежи и задачи.<br /><b>Подтвердите удаление.</b></center>',
                butSubmit:'Удалить',
                submit:submit
            });
            function submit() {
                var send = {
                    op:'client_del',
                    id:CLIENT.id
                };
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        _msg('Клиент удален!');
                        location.href = URL + '&p=client';
                    } else
                        dialog.abort();
                }, 'json');
            }
        });
    }

    if($('#zayavAdd').length > 0) {
        $('#client_id').clientSel({add:1});
        $('#product_id').vkSel({
            width:142,
            display:'inline-block',
            title0:'Изделие не указано',
            spisok:product
        });
        $('#comm').autosize();
        $('.vkCancel').click(function() {
            location.href = URL + '&p=' + $(this).attr('val');
        });
        $('.vkButton').click(function () {
            var send = {
                op:'zayav_add',
                client_id:$('#client_id').val(),
                nomer_dog:$('#nomer_dog').val(),
                nomer_vg:$('#nomer_vg').val(),
                product_id:$('#product_id').val(),
                adres_set:$('#adres_set').val(),
                comm:$('#comm').val()
            };

            var msg = '';
            if(send.client == 0) msg = 'Не выбран клиент';
            else {
                $(this).addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    location.href = URL + '&p=zayav&d=info&id=' + res.id;
                }, 'json');
            }

            if(msg)
                $(this).vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    top:-48,
                    left:201,
                    indent:30,
                    remove:1,
                    show:1,
                    correct:0
                });
        });
    }
});