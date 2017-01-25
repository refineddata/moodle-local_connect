// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Get file tree from Adobe Connect
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'jqueryui'], function ($, ui) {

    return {
        init: function () {

            $(document).ready(function () {

                !function (e, t) {
                    var i;
                    return i = function () {
                        function t(t, i, n) {
                            var r, s;
                            r = e(t), s = {
                                root: "/",
                                script: "/files/filetree",
                                folderEvent: "click",
                                expandSpeed: 500,
                                collapseSpeed: 500,
                                expandEasing: "swing",
                                collapseEasing: "swing",
                                multiFolder: !0,
                                loadMessage: "Loading...",
                                errorMessage: "Unable to get file tree information",
                                multiSelect: !1,
                                onlyFolders: !1,
                                onlyFiles: !1
                            }, this.jqft = {container: r}, this.options = e.extend(s, i), this.callback = n, r.html('<ul class="jqueryFileTree start"><li class="wait">' + this.options.loadMessage + "<li></ul>"), this.showTree(r, escape(this.options.root))
                        }

                        return t.prototype.showTree = function (t, i) {
                            var n, r, s, a;
                            return n = e(t), a = this.options, r = this, n.addClass("wait"), e(".jqueryFileTree.start").remove(), s = {
                                dir: i,
                                onlyFolders: a.onlyFolders,
                                onlyFiles: a.onlyFiles,
                                multiSelect: a.multiSelect
                            }, e.ajax({url: a.script, type: "POST", dataType: "HTML", data: s}).done(function (t) {
                                var s;
                                return n.find(".start").html(""), n.removeClass("wait").append(t), a.root === i ? n.find("UL:hidden").show("undefined" != typeof callback && null !== callback) : (void 0 === jQuery.easing[a.expandEasing] && (console.log("Easing library not loaded. Include jQueryUI or 3rd party lib."), a.expandEasing = "swing"), n.find("UL:hidden").slideDown({
                                    duration: a.expandSpeed,
                                    easing: a.expandEasing
                                })), s = e('[rel="' + decodeURIComponent(i) + '"]').parent(), a.multiSelect && s.children("input").is(":checked") && s.find("ul li input").each(function () {
                                    return e(this).prop("checked", !0), e(this).parent().addClass("selected")
                                }), r.bindTree(n)
                            }).fail(function () {
                                return n.find(".start").html(""), n.removeClass("wait").append("<p>" + a.errorMessage + "</p>"), !1
                            })
                        }, t.prototype.bindTree = function (t) {
                            var i, n, r, s, a, l;
                            return i = e(t), a = this.options, s = this.jqft, n = this, r = this.callback, l = /^\/.*\/$/i, i.find("LI A").on(a.folderEvent, function () {
                                var t, i;
                                return t = {}, t.li = e(this).closest("li"), t.type = null != (i = t.li.hasClass("directory")) ? i : {directory : "file"}, t.value = e(this).text(), t.rel = e(this).prop("rel"), t.container = s.container, e(this).parent().hasClass("directory") ? e(this).parent().hasClass("collapsed") ? (n._trigger(e(this), "filetreeexpand", t), a.multiFolder || (e(this).parent().parent().find("UL").slideUp({
                                    duration: a.collapseSpeed,
                                    easing: a.collapseEasing
                                }), e(this).parent().parent().find("LI.directory").removeClass("expanded").addClass("collapsed")), e(this).parent().removeClass("collapsed").addClass("expanded"), e(this).parent().find("UL").remove(), n.showTree(e(this).parent(), e(this).attr("rel").match(l)[0]), n._trigger(e(this), "filetreeexpanded", t)) : (n._trigger(e(this), "filetreecollapse", t), e(this).parent().find("UL").slideUp({
                                    duration: a.collapseSpeed,
                                    easing: a.collapseEasing
                                }), e(this).parent().removeClass("expanded").addClass("collapsed"), n._trigger(e(this), "filetreecollapsed", t)) : (a.multiSelect ? e(this).parent().find("input").is(":checked") ? (e(this).parent().find("input").prop("checked", !1), e(this).parent().removeClass("selected")) : (e(this).parent().find("input").prop("checked", !0), e(this).parent().addClass("selected")) : (s.container.find("li").removeClass("selected"), e(this).parent().addClass("selected")), n._trigger(e(this), "filetreeclicked", t), "function" == typeof r && r(e(this).attr("rel"))), !1
                            }), "click" !== a.folderEvent.toLowerCase ? i.find("LI A").on("click", function () {
                                return !1
                            }) : void 0
                        }, t.prototype._trigger = function (t, i, n) {
                            var r;
                            return r = e(t), r.trigger(i, n)
                        }, t
                    }(), e.fn.extend({
                        fileTree: function (t, n) {
                            return this.each(function () {
                                var r, s;
                                return r = e(this), s = r.data("fileTree"), s || r.data("fileTree", s = new i(this, t, n)), "string" == typeof t ? s[option].apply(s) : void 0
                            })
                        }
                    })
                }($, window);

                get_mod_connectmeeting_filter();
                get_mod_connectquiz_filter();
                get_mod_connectslide_filter();
                get_rtrecording_filter();

                $('#id_generate').click(function () {
                    var connectUrl = Math.random().toString(36).slice(2, 8);
                    connectUrl = connectUrl.replace(/^[0-9]/, 'a');
                    connect_get_sco_by_url(connectUrl);
                });

                $('#id_url').keyup(function () {
                    var connectUrl = $(this).val();
                    delay(function () {
                        if (typeof $("#id_browse").data('rtrecording') !== 'undefined'){
                            connectrecording_get_sco_by_url(connectUrl);
                        } else {
                            connect_get_sco_by_url(connectUrl);
                        }
                    }, 1000);
                });

                $("#id_url").blur(function () {
                    var connectUrl = $(this).val();
                    if (typeof $("#id_browse").data('rtrecording') !== 'undefined'){
                        connectrecording_get_sco_by_url(connectUrl);
                    } else {
                        connect_get_sco_by_url(connectUrl);
                    }
                });

                if ($("#id_url").length > 0) {
                    $('#id_name').keyup(function () {
                        if ($(this).hasClass('do-not-check')) {
                            return;
                        }
                        var connectName = $(this).val();
                        delay(function () {
                            connect_get_sco_by_name(connectName);
                        }, 3000);
                    });
                }

                if ($("#id_url").length > 0) {
                    $("#id_name").blur(function () {
                        if ($(this).hasClass('do-not-check')) {
                            return;
                        }
                        var connectName = $(this).val();
                        connect_get_sco_by_name(connectName);
                    });
                }

                $('#id_browse').click(function () {
                    var tag = $("<div id='browseurl_window'></div>");

                    tag.fileTree({
                        // root: '/some/folder/',
                        script: window.wwwroot + '/local/connect/ajax/jqueryFileTree.php',
                        expandSpeed: 1000,
                        collapseSpeed: 1000,
                        multiFolder: false
                    }, function (connectUrl) {
                        $('#browseurl_window').dialog("destroy");
                        $('#id_url').val(connectUrl);
                        if (typeof $("#id_browse").data('rtrecording') !== 'undefined'){
                            connectrecording_get_sco_by_url(connectUrl);
                        } else {
                            connect_get_sco_by_url(connectUrl);
                        }

                    }).dialog({
                        title: window.browsetitle,
                        modal: true,
                        width: '80%',
                        minHeight: 450,
                        height: 450,
                        resizable: false
                    }).dialog('open');
                });

                var delay = (function () {
                    var timer = 0;
                    return function (callback, ms) {
                        clearTimeout(timer);
                        timer = setTimeout(callback, ms);
                    };
                })();

                if (typeof $("#id_browse").data('rtrecording') !== 'undefined'){
                    hideRemindersSection();
                }
                if (typeof $("#id_browse").data('rtrecording') !== 'undefined') {
                    hidePositionGrading();
                    $('#id_start_enabled').change(function () {
                        hideRemindersSection();
                        hidePositionGrading();
                    });
                }

                hideGradingFields();
                $('#id_detailgrading').change(function () {
                    hideGradingFields();
                });

                if (typeof $("#id_browse").data('rtrecording') !== 'undefined') {
                    connectUrl = $('#id_url').val();
                    if (connectUrl) {
                        checkIfVpRecording(connectUrl);
                    }
                }

                // Course page - Connect Meeting
                $('body').on("click", "#connectmeeting-update-from-adobe", function (event) {
                    event.preventDefault();
                    var block = $(this);
                    var connectmeeting_id = block.data('connectmeetingid');
                    $('#connectmeetingcontent' + connectmeeting_id).html('');
                    $('#connectmeetingcontent' + connectmeeting_id).addClass('rt-loading-image');
                    $.ajax({
                        url: window.wwwroot + "/mod/connectmeeting/ajax/connectmeeting_callback.php",
                        dataType: "html",
                        data: {
                            connectmeeting_id: connectmeeting_id,
                            update_from_adobe: 1,
                        }
                    }).done(function (data) {
                        $('#connectmeetingcontent' + connectmeeting_id).removeClass('rt-loading-image');
                        $('#connectmeetingcontent' + connectmeeting_id).html(data);
                    });
                });

                // Course page - Connect quiz
                $('body').on("click", "#connectquiz-update-from-adobe", function(event){
                    event.preventDefault();
                    var block = $(this);
                    var connectquiz_id = block.data('connectquizid');
                    $('#connectquizcontent' + connectquiz_id ).html('');
                    $('#connectquizcontent' + connectquiz_id ).addClass('rt-loading-image');
                    $.ajax({
                        url: window.wwwroot + "/mod/connectquiz/ajax/connectquiz_callback.php",
                        dataType: "html",
                        data: {
                            connectquiz_id: connectquiz_id,
                            update_from_adobe: 1,
                        }
                    }).done(function (data) {
                        $('#connectquizcontent' + connectquiz_id ).removeClass('rt-loading-image');
                        $('#connectquizcontent' + connectquiz_id ).html(data);
                    });
                });

                // Course page - Connect slide
                $('body').on("click", "#connectslide-update-from-adobe", function(event){
                    event.preventDefault();
                    var block = $(this);
                    var connectslide_id = block.data('connectslideid');
                    $('#connectslidecontent' + connectslide_id ).html('');
                    $('#connectslidecontent' + connectslide_id ).addClass('rt-loading-image');
                    $.ajax({
                        url: window.wwwroot + "/mod/connectslide/ajax/connectslide_callback.php",
                        dataType: "html",
                        data: {
                            connectslide_id: connectslide_id,
                            update_from_adobe: 1,
                        }
                    }).done(function (data) {
                        $('#connectslidecontent' + connectslide_id ).removeClass('rt-loading-image');
                        $('#connectslidecontent' + connectslide_id ).html(data);
                    });
                });

                // Course page - Connect recording
                $('body').on("click", "#rtrecording-update-from-adobe", function(event){
                    event.preventDefault();
                    var block = $(this);
                    var rtrecording_id = block.data('rtrecordingid');
                    $('#recordingcontent' + rtrecording_id ).html('');
                    $('#recordingcontent' + rtrecording_id ).addClass('rt-loading-image');
                    $.ajax({
                        url: window.wwwroot + "/mod/rtrecording/ajax/rtrecording_callback.php",
                        dataType: "html",
                        data: {
                            rtrecording_id: rtrecording_id,
                            update_from_adobe: 1,
                        }
                    }).done(function (data) {
                        $('#recordingcontent' + rtrecording_id ).removeClass('rt-loading-image');
                        $('#recordingcontent' + rtrecording_id ).html(data);
                    });
                });
            });

            function connect_get_sco_by_url(connectUrl) {
                if ($("input[name='typeisvideo']").val()) {
                    return;
                }
                $("#id_ajax_spin").remove();
                $('#id_generate').after(' <span id="id_ajax_spin" class="rt-loading-image"></span>');
                $.post(window.wwwroot + "/mod/connectmeeting/ajax/ajax.php",
                    {
                        action: "connect_get_sco_by_url",
                        url: connectUrl
                    }
                , function (data) {
                    $('#id_telephony').show();
                    $('#id_template').show();
                    if (data.refined_noauth) {
                        $('#id_url').val('');
                        $('#id_name').val('');
                        add_alert('danger', data.refined_noauth_message);
                    } else if (data.error) {
                        add_alert('danger', data.error);
                    } else if (data.response == 'connect_not_update') {
                        if (typeof M.str.connectmeeting.eeting.connect_not_update != 'undefined') {
                            var msg = M.str.connectmeeting.typelistmeeting + M.str.connectmeeting.connect_not_update;
                            add_alert('danger', msg);
                        }
                    } else if (data.response == 'no-data' || data.response.fixedurl) {
                        if (data.response.fixedurl) {
                            connectUrl = data.response.fixedurl;
                        }
                        $('#id_url').val(connectUrl);
                        if (typeof M.str.connectmeeting.whensaved != 'undefined') {
                            var msg = M.str.connectmeeting.notfound + M.str.connectmeeting.typelistmeeting + M.str.connectmeeting.whensaved;
                            add_alert('success', msg);
                        } else if (typeof M.str.connectmeeting.connect_not_update != 'undefined') {
                            var msg = M.str.connectmeeting.notfound + M.str.connectmeeting.typelistmeeting + M.str.connectmeeting.connect_not_update;
                            add_alert('danger', msg);
                        }
                        $('#id_name').removeClass('do-not-check');
                    } else if (data.response.name) {
                        $('#id_url').val(data.response.url.replace(/\//g, '')); // in case the url was cleaned
                        $('#id_name').val(data.response.name).addClass('do-not-check');
                        if (typeof tinymce !== 'undefined') {
                            tinymce.get("id_introeditor").setContent(data.response.desc);
                        } else {
                            $('#id_introeditoreditable').html(data.response.desc);
                        }
                        var startDate = new Date(data.response.start * 1000);
                        $('#id_start_day').val(startDate.getDate());
                        $('#id_start_month').val(startDate.getMonth() + 1);
                        $('#id_start_year').val(startDate.getFullYear());
                        $('#id_start_hour').val(startDate.getHours());
                        $('#id_start_minute').val(startDate.getMinutes());
                        $('#id_duration').val(data.response.end - data.response.start);
                        $('#id_telephony').hide();
                        $('#id_template').hide();
                    } else if (data.response == 'denied') {
                        $('#id_url').val('');
                        add_alert('danger', 'Access Denied, you do not have access to this url');
                    } else if (data.response == 'folderdenied') {
                        $('#id_url').val('');
                        add_alert('danger', 'Access Denied, you do not have access to create a meeting in the default folder');
                    } else {
                        add_alert('danger', data);
                    }
                }).done(function () {
                    $("#id_ajax_spin").remove();
                });
            }

            function connect_get_sco_by_name(connectName) {
                if ($("input[name='typeisvideo']").val()) {
                    return;
                }
                $("#id_ajax_spin_name").remove();
                $('#id_name').after(' <span id="id_ajax_spin_name" class="rt-loading-image"></span>');
                $.post(
                    window.wwwroot + "/mod/connectmeeting/ajax/ajax.php",
                    {
                        action: "connect_get_sco_by_name",
                        name: connectName
                    }
                , function (data) {
                    if (data.refined_noauth) {
                        $('#id_url').val('');
                        $('#id_name').val('');
                    } else if (data.error) {
                        add_name_alert('danger', data.error);
                    } else if (data.response == 'no-data') {
                        var msg = 'The requested name is not found and can be used';
                        add_name_alert('success', msg);
                    } else {
                        var msg = 'The requested name ( ' + connectName + ' ) is found and can not be used';
                        add_name_alert('danger', msg);
                        $('#id_name').val('');
                    }
                }).done(function () {
                    $("#id_ajax_spin_name").remove();
                });
            }

            function add_alert(type, msg) {
                $("#fgroup_id_urlgrp_alert").remove();
                var alertmsg = '<div class="fitem" id="fgroup_id_urlgrp_alert">';
                alertmsg += '<div class="felement fstatic alert alert-' + type + ' alert-dismissible">';
                alertmsg += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>';
                alertmsg += msg;
                alertmsg += '</div>';
                alertmsg += '</div>';
                $('#id_url').parent().parent().parent().parent().after( alertmsg );
                window.setTimeout(function () {
                    $("#fgroup_id_urlgrp_alert").fadeTo(500, 0).slideUp(500, function () {
                        $(this).remove();
                    });
                }, 5000);
            }

            function add_name_alert(type, msg) {
                $("#name_alert").remove();
                var alertmsg = '<div class="fitem" id="name_alert">';
                alertmsg += '<div class="felement fstatic alert alert-' + type + ' alert-dismissible">';
                alertmsg += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>';
                alertmsg += msg;
                alertmsg += '</div>';
                alertmsg += '</div>';
                $('#id_name').after( alertmsg );
                window.setTimeout(function () {
                    $("#name_alert").fadeTo(500, 0).slideUp(500, function () {
                        $(this).remove();
                    });
                }, 5000);
            }

            function hideGradingFields() {
                var value = $('#id_detailgrading').val();
                if (value == 0) {
                    $('#id_threshold_1').parent().parent().parent().parent().hide();
                    $('#id_threshold_2').parent().parent().parent().parent().hide();
                    $('#id_threshold_3').parent().parent().parent().parent().hide();

                    $('#id_vpthreshold_1').parent().parent().parent().parent().hide();
                    $('#id_vpthreshold_2').parent().parent().parent().parent().hide();
                    $('#id_vpthreshold_3').parent().parent().parent().parent().hide();
                } else if (value == 1) {
                    $('#id_threshold_1').parent().parent().parent().parent().show();
                    $('#id_threshold_2').parent().parent().parent().parent().show();
                    $('#id_threshold_3').parent().parent().parent().parent().show();

                    $('#id_vpthreshold_1').parent().parent().parent().parent().hide();
                    $('#id_vpthreshold_2').parent().parent().parent().parent().hide();
                    $('#id_vpthreshold_3').parent().parent().parent().parent().hide();
                } else if (value == 3) {
                    $('#id_threshold_1').parent().parent().parent().parent().hide();
                    $('#id_threshold_2').parent().parent().parent().parent().hide();
                    $('#id_threshold_3').parent().parent().parent().parent().hide();

                    $('#id_vpthreshold_1').parent().parent().parent().parent().show();
                    $('#id_vpthreshold_2').parent().parent().parent().parent().show();
                    $('#id_vpthreshold_3').parent().parent().parent().parent().show();
                }
            }

            function add_mod_connectmeeting_filter_alert(block, type, msg) {
                var alertmsg = '<div class="fitem" id="fgroup_id_urlgrp_alert">';
                alertmsg += '<div class="felement fstatic alert alert-' + type + ' alert-dismissible">';
                alertmsg += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>';
                alertmsg += msg;
                alertmsg += '</div>';
                alertmsg += '</div>';
                block.html( alertmsg );
            }

            function get_mod_connectmeeting_filter() {
                $('.connectmeeting_display_block').each(function (index) {
                    var block = $(this);
                    var acurl = block.data('acurl');
                    var sco = block.data('sco');
                    var courseid = block.data('courseid');
                    block.removeClass('connectmeeting_display_block').addClass('connect_display_block_done');
                    $.ajax({
                        url: window.wwwroot + "/mod/connectmeeting/ajax/connectmeeting_callback.php",
                        dataType: "html",
                        data: {
                            acurl: acurl,
                            sco: sco,
                            courseid: courseid,
                            options: encodeURIComponent(block.data('options')),
                            frommymeetings: block.data('frommymeetings'),
                            frommyrecordings: block.data('frommyrecordings')
                        }
                    }).done(function (data) {
                        block.html(data);
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        add_mod_connectmeeting_filter_alert(block, 'danger', jqXHR.status + " " + jqXHR.statusText);
                    });
                });
            }

            function add_mod_connectquiz_filter_alert(block, type, msg) {
                var alertmsg = '<div class="fitem" id="fgroup_id_urlgrp_alert">';
                alertmsg += '<div class="felement fstatic alert alert-' + type + ' alert-dismissible">';
                alertmsg += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>';
                alertmsg += msg;
                alertmsg += '</div>';
                alertmsg += '</div>';
                block.html( alertmsg );
            }

            function get_mod_connectquiz_filter() {
                $('.connectquiz_display_block').each(function (index) {
                    var block = $(this);
                    var acurl = block.data('acurl');
                    var sco = block.data('sco');
                    var courseid = block.data('courseid');
                    block.removeClass('connectquiz_display_block').addClass('connectquiz_display_block_done');
                    $.ajax({
                        url: window.wwwroot + "/mod/connectquiz/ajax/connectquiz_callback.php",
                        dataType: "html",
                        data: {
                            acurl: acurl,
                            sco: sco,
                            courseid: courseid,
                            options: encodeURIComponent(block.data('options')),
                            frommymeetings: block.data('frommymeetings'),
                            frommyrecordings: block.data('frommyrecordings')
                        }
                    }).done(function (data) {
                        block.html(data);
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        add_mod_connectquiz_filter_alert(block, 'danger', jqXHR.status + " " + jqXHR.statusText);
                    });
                });
            }

            function add_mod_connectslide_filter_alert(block, type, msg) {
                var alertmsg = '<div class="fitem" id="fgroup_id_urlgrp_alert">';
                alertmsg += '<div class="felement fstatic alert alert-' + type + ' alert-dismissible">';
                alertmsg += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>';
                alertmsg += msg;
                alertmsg += '</div>';
                alertmsg += '</div>';
                block.html( alertmsg );
            }

            function get_mod_connectslide_filter() {
                $('.connectslide_display_block').each(function (index) {
                    var block = $(this);
                    var acurl = block.data('acurl');
                    var sco = block.data('sco');
                    var courseid = block.data('courseid');
                    block.removeClass('connectslide_display_block').addClass('connectslide_display_block_done');
                    $.ajax({
                        url: window.wwwroot + "/mod/connectslide/ajax/connectslide_callback.php",
                        dataType: "html",
                        data: {
                            acurl: acurl,
                            sco: sco,
                            courseid: courseid,
                            options: encodeURIComponent(block.data('options')),
                            frommymeetings: block.data('frommymeetings'),
                            frommyrecordings: block.data('frommyrecordings')
                        }
                    }).done(function (data) {
                        block.html(data);
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        add_mod_connectslide_filter_alert(block, 'danger', jqXHR.status + " " + jqXHR.statusText);
                    });
                });
            }

            function get_rtrecording_filter() {
                $('.rtrecording_display_block').each(function (index) {
                    var block = $(this);
                    var acurl = block.data('acurl');
                    var courseid = block.data('courseid');
                    var rtrecording_id = block.data('rtrecording_id');
                    block.removeClass('rtrecording_display_block').addClass('rtrecording_display_block_done');
                    $.ajax({
                        url: window.wwwroot + "/mod/rtrecording/ajax/rtrecording_callback.php",
                        dataType: "html",
                        data: {
                            acurl: acurl,
                            courseid: courseid,
                            rtrecording_id: rtrecording_id,
                            options: encodeURIComponent(block.data('options')),
                        }
                    }).done(function (data) {
                        block.html(data);
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        add_rtrecording_filter_alert(block, 'danger', jqXHR.status + " " + jqXHR.statusText);
                    });
                });
            }

            function add_rtrecording_filter_alert(block, type, msg) {
                var alertmsg = '<div class="fitem" id="fgroup_id_urlgrp_alert">';
                alertmsg += '<div class="felement fstatic alert alert-' + type + ' alert-dismissible">';
                alertmsg += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>';
                alertmsg += msg;
                alertmsg += '</div>';
                alertmsg += '</div>';
                block.html( alertmsg );
            }

            // Connect recording

            function connectrecording_get_sco_by_url(connectUrl) {
                if( $( "input[name='typeisvideo']" ).val() ){
                    return;
                }

                $("#id_ajax_spin").remove();
                $('#id_browse').after(' <span id="id_ajax_spin" class="rt-loading-image"></span>');
                $.post(window.wwwroot + "/mod/rtrecording/ajax/ajax.php",
                    {

                        action: "connect_get_sco_by_url",
                        url: connectUrl
                    }
                , function (data) {
                    if (data.refined_noauth) {
                        $('#id_url').val('');
                        $('#id_name').val('');
                        add_alert('danger', data.refined_noauth_message);
                    } else if (data.error) {
                        add_alert('danger', data.error);
                    } else if (data.response == 'connect_not_update') {
                        if (typeof M.str.connect.connect_not_update != 'undefined') {
                            var msg = M.str.connect.typelistmeeting + M.str.connect.connect_not_update;
                            add_alert('danger', msg);
                        }
                    } else if (data.response == 'no-data' || data.response.fixedurl ) {
                        if( data.response.fixedurl ){
                            connectUrl = data.response.fixedurl;
                        }
                        $('#id_url').val(connectUrl);
                        if (typeof M.str.connect.whensaved != 'undefined') {
                            var msg = M.str.connect.notfound + M.str.connect.typelistmeeting + M.str.connect.whensaved;
                            add_alert('success', msg);
                        } else if (typeof M.str.connect.connect_not_update != 'undefined') {
                            var msg = M.str.connect.notfound + M.str.connect.typelistmeeting + M.str.connect.connect_not_update;
                            add_alert('danger', msg);
                        }
                        $('#id_name').removeClass('do-not-check');
                    } else if (data.response.icon != 'archive' ){
                        $('#id_url').val('');
                        $('#id_name').val('');
                        add_alert( 'danger', 'Provided Url must be for a recording' );
                    } else if (data.response.name) {
                        $('#id_url').val(data.response.url.replace(/\//g,'')); // in case the url was cleaned
                        $('#id_name').val(data.response.name).addClass('do-not-check');
                        if( typeof tinymce !== 'undefined' ){
                            tinymce.get("id_introeditor").setContent(data.response.desc);
                        }else{
                            $('#id_introeditoreditable').html(data.response.desc);
                        }
                    } else if( data.response == 'denied' ){
                        $('#id_url').val('');
                        add_alert('danger', 'Access Denied, you do not have access to this url' );
                    } else {
                        add_alert('danger', data);
                    }

                    checkIfVpRecording( connectUrl );

                }).done(function () {
                    $("#id_ajax_spin").remove();
                });
            }

            function checkIfVpRecording( connectUrl ){
                // check if VP recording or not
                $.post( window.wwwroot + "/mod/rtrecording/ajax/ajax.php",
                    {
                        action: "connect_check_if_vp_recording",
                        url: connectUrl
                    }
                , function (data) {
                    if( data.response ){
                        $("#id_detailgrading").attr("disabled", false);
                    }else{
                        $("#id_detailgrading").val(0);
                        $("#id_detailgrading").attr("disabled", true);
                        $('#id_threshold_1').parent().parent().parent().parent().hide();
                        $('#id_threshold_2').parent().parent().parent().parent().hide();
                        $('#id_threshold_3').parent().parent().parent().parent().hide();
                        $('#id_vpthreshold_1').parent().parent().parent().parent().hide();
                        $('#id_vpthreshold_2').parent().parent().parent().parent().hide();
                        $('#id_vpthreshold_3').parent().parent().parent().parent().hide();
                    }
                });
            }

            function hideRemindersSection(){
                if( $('#id_start_enabled').is(':checked') ){
                    $('#id_remhdr').show();
                }else{
                    $('#id_remhdr').hide();
                }
            }

            function hidePositionGrading(){
                if( $('#id_start_enabled').is(':checked') ){
                    $("#id_detailgrading").val(0);
                    $("#id_detailgrading option[value=1]").hide();
                }else{
                    $("#id_detailgrading option[value=1]").show();
                }
            }

        }
    };

});