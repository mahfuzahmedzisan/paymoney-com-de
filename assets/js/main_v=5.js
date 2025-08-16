var locale = null;

//event doesn't work
var messagesLoaded = false;

let lang = getCookie("lang");
if (lang == null) {
    lang = "en";
}

$.ajax({
    type: "GET",
    url: "/api/getLocale?v=2&lang=" + lang,
    success: function (response) {
        locale = response;
        messagesLoaded = true;

        checkCookiesAccepted();
    }
});

function getMessage(key, replacers) {
    let message = locale[key];

    if (replacers && replacers.length > 0) {
        replacers.forEach((replacer, index) => {
            message = message.replace(`{${index}}`, replacer);
        });
    }

    return message;
}

function checkCookiesAccepted() {
    const cookiesAccepted = getCookie("cookies_accepted");

    if (!cookiesAccepted || cookiesAccepted === "") {
        const acceptMessage = getMessage('cookies.accept.message');
        const privacyPolicy = getMessage('cookies.privacy.policy');
        const acceptButton = getMessage('cookies.accept.button');

        $("footer").prepend(`
            <div class="cookie">
               <div class="cookie__content">
                  <div class="cookie__text">
                     ${acceptMessage}
                     <a href="/privacy-policy" class="">
                     ${privacyPolicy}</a>
                  </div>
                  <div class="spacer"></div>
                  <button type="button" onclick="acceptCookies(event)" class="cookie__btn v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--default"><span class="v-btn__content">${acceptButton}</span></button>
               </div>
            </div>
        `);
    }
}

function getCookie(name) {
    let cookieArr = document.cookie.split(";");

    for(let i = 0; i < cookieArr.length; i++) {
        let cookiePair = cookieArr[i].split("=");

        if(name === cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }

    return null;
}

function setCookie(cookieName, cookieValue, daysToExpire) {
    let expires = "";
    if (daysToExpire) {
        const date = new Date();
        date.setTime(date.getTime() + (daysToExpire * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = cookieName + "=" + cookieValue + expires + "; path=/";
}

function acceptCookies() {
    event.preventDefault();

    setCookie("cookies_accepted", "true", 999);
    $(".cookie").remove();
}

//mobile app start
const banner = $('#mobile-app-banner');

$('#mobile-app-btn').on('click', () => {
    banner.css('display', 'flex');
});

$('#mobile-app-close').on('click', () => {
    banner.addClass('custom-popup-fade-out');

    setTimeout(() => {
        banner.css('display', 'none');
        banner.removeClass('custom-popup-fade-out')
    }, 500);
});

document.addEventListener('click', () => {
    if (banner.css('display') !== 'none' && !event.target.closest('.v-dialog__content') && event.target.id !== 'mobile-app-btn') {
        banner.css('display', 'none');
    }
});

document.addEventListener('click', function() {
    if (!event.target.closest('.main-menu__section__menu') && !event.target.closest('.main-menu__section__nav-button')
        && !event.target.closest('.main-menu__section__profile') && !event.target.closest('.mobile-menu-button') && !event.target.closest('.mobile-menu') && !event.target.closest('.assets-hide-show')) {
        $('.main-menu__section__nav-button').each((index, btn) => {
            $(btn).removeClass('active');
        });
        $('.main-menu__section__menu').each((index, menu) => {
            $(menu).css('display', 'none');
        });
        $(".mobile-menu-button > div").removeClass('open');
        $(".mobile-menu").css('display', 'none');
        $('.menu-lang').css('display', 'none');
    }
});
//mobile app end

//header start
//mobile start

$(".main-menu__section").on('click', function (event) {
    if (event.target.closest('.main-menu__section__menu')) {
        return;
    }

    const el = $(this).find('.main-menu__section__menu');
    if ($(this).hasClass('lang-button')) {
        return;
    }
    const active = el.css('display') !== 'none';

    $('.main-menu__section__nav-button').each((index, btn) => {
        $(btn).removeClass('active');
    });
    $('.main-menu__section__menu').each((index, menu) => {
        $(menu).css('display', 'none');
    });

    $(".mobile-menu-button > div").removeClass('open');
    $(".mobile-menu").css('display', 'none');
    $('.menu-lang').css('display', 'none');

    $('.mobile-menu__item__panel').each(function() {
        const btnWrapper = $(this);

        btnWrapper.removeClass('v-expansion-panel--active');
        btnWrapper.removeClass('v-item--active');

        const btn = btnWrapper.find('button');
        btn.removeClass('v-expansion-panel-header--active');
        btn.attr('aria-expanded', 'false');

        btnWrapper.find('a').each(function() {
            $(this).css('display', 'none');
        });
    });

    if (!active) {
        if (el.hasClass('main-menu__section-flex')) {
            el.css('display', 'flex');
        } else {
            el.css('display', 'block');
        }
    }
});
//mobile end

$(".main-menu__section").on('mouseenter', function (event) {
    if (window.innerWidth < 1400) {
        return;
    }

    $(this).find('.main-menu__section__nav-button').addClass('active');
    const el = $(this).find('.main-menu__section__menu');
    if (el.hasClass('main-menu__section-flex')) {
        el.css('display', 'flex');
    } else {
        el.css('display', 'block');
    }
});

$(".main-menu__section").on('mouseleave', function (event) {
    if (window.innerWidth < 1400) {
        return;
    }

    $(this).find('.main-menu__section__nav-button').removeClass('active');
    $(this).find('.main-menu__section__menu').css('display', 'none');
});

$(".nav-button-lang").on('click', function () {
    const has = $(this).hasClass('active');

    $('.main-menu__section__nav-button').each((index, btn) => {
        $(btn).removeClass('active');
    });
    $('.main-menu__section__menu').each((index, menu) => {
        $(menu).css('display', 'none');
    });

    $(".mobile-menu-button > div").removeClass('open');
    $(".mobile-menu").css('display', 'none');
    $('.menu-lang').css('display', 'none');

    $('.mobile-menu__item__panel').each(function() {
        const btnWrapper = $(this);

        btnWrapper.removeClass('v-expansion-panel--active');
        btnWrapper.removeClass('v-item--active');

        const btn = btnWrapper.find('button');
        btn.removeClass('v-expansion-panel-header--active');
        btn.attr('aria-expanded', 'false');

        btnWrapper.find('a').each(function() {
            $(this).css('display', 'none');
        });
    });

    if (has) {
        $(this).removeClass('active');
        $('.menu-lang').css('display', 'none');
    } else {
        $(this).addClass('active');
        $('.menu-lang').css('display', 'flex');
    }
});

$(".mobile-menu-button").on('click', function () {
    const buttonIcon = $(this).find('div');

    const has = buttonIcon.hasClass('open');
    $('.main-menu__section__nav-button').each((index, btn) => {
        $(btn).removeClass('active');
    });
    $('.main-menu__section__menu').each((index, menu) => {
        $(menu).css('display', 'none');
    });
    $(".mobile-menu-button > div").removeClass('open');
    $(".mobile-menu").css('display', 'none');
    $('.menu-lang').css('display', 'none');

    $('.mobile-menu__item__panel').each(function() {
        const btnWrapper = $(this);

        btnWrapper.removeClass('v-expansion-panel--active');
        btnWrapper.removeClass('v-item--active');

        const btn = btnWrapper.find('button');
        btn.removeClass('v-expansion-panel-header--active');
        btn.attr('aria-expanded', 'false');

        btnWrapper.find('a').each(function() {
            $(this).css('display', 'none');
        });
    });

    if (has) {
        buttonIcon.removeClass('open');
        $(".mobile-menu").css('display', 'none');
    } else {
        buttonIcon.addClass('open');
        $(".mobile-menu").css('display', 'block');
    }

    $(".main-menu__section > button").each(el => {
        $(el).removeClass('active');
        $('.menu-lang').css('display', 'none');
    })
});

$(".v-expansion-panel-header").on('click', function () {
    const opened = $(this).hasClass('v-expansion-panel-header--active');
    const panel = $(this).parent();
    const content = panel.find('.v-expansion-panel-content');

    if (opened) {
        $(this).removeClass('v-expansion-panel-header--active');
        panel.removeClass('v-expansion-panel--active v-item--active');
        panel.attr('aria-expanded', 'false');
        content.css({
            'max-height': '0',
            'overflow': 'hidden',
            'padding': '0',
            'margin': '0',
            'display': 'none'
        });
    } else {
        content.css({
            'display': 'block',
            'padding': '',
            'margin': ''
        });
        const contentHeight = content.prop('scrollHeight');
        $(this).addClass('v-expansion-panel-header--active');
        panel.addClass('v-expansion-panel--active v-item--active');
        panel.attr('aria-expanded', 'true');
        content.css({
            'max-height': contentHeight + 'px',
            'overflow': 'hidden'
        });
    }
});

$(".footer-title__wrapper").on('click', function() {
    const section = $(this).closest('.footer-section');

    if (section.hasClass('active')) {
        section.removeClass('active');
    } else {
        section.addClass('active');
    }
});

$("#balances-general").on('click', function() {
    window.scrollTo(0, 0);
});
//header end

function copyToClipboard(text) {
    var textarea = document.createElement("textarea");
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    document.body.removeChild(textarea);
}

function copyWithButton(copyCodeBtn, copyMsg, copyFunction) {
    copyCodeBtn.on('mousedown', function (event) {
        if (event.button === 0) {
            copyFunction();
        }
    });

    copyCodeBtn.on('mouseup', function (event) {
        if (event.button === 0) {
            noti(copyMsg, 'success');
        }
    });
}

const usdBalance = $("#usd-balance");
const btcBalance = $("#btc-balance");
const spotBalance = $("#spot-balance");
const marginBalance = $("#margin-balance");
const futuresBalance = $("#futures-balance");
const earnBalance = $("#earn-balance");

var balanceShowed = getCookie("balance_showed");
const assetsHideShow = $("#assets-hide-show");

if (balanceShowed && balanceShowed !== 'true') {
    hideBalance();
}

function hideBalance() {
    usdBalance.html('***** USD');
    btcBalance.html('≈ ***** BTC');
    spotBalance.html('$*.**');
    marginBalance.html('$*.**');
    futuresBalance.html('$*.**');
    earnBalance.html('$*.**');
    assetsHideShow.attr('src', 'src="../assets/img/show.svg"');
}

function showBalance() {
    usdBalance.html(usdBalance.attr('usd-balance') + ' USD');
    btcBalance.html('≈ ' + btcBalance.attr('btc-balance') + ' BTC');
    spotBalance.html('$0.00');
    marginBalance.html('$0.00');
    futuresBalance.html('$0.00');
    earnBalance.html('$0.00');
    assetsHideShow.attr('src', 'src="../hide/img/show.svg"');
}

function onAssetsHideShow(event) {
    event.preventDefault();

    balanceShowed = getCookie("balance_showed");

    if (balanceShowed && balanceShowed !== 'true') {
        setCookie("balance_showed", "true", 30);
        showBalance();
    } else {
        setCookie("balance_showed", "false", 30);
        hideBalance();
    }
}

function formatBytes(bytes, decimals = 2) {
    if (!+bytes) return "0 Bytes";
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    const index = Math.floor(Math.log(bytes) / Math.log(1024));
    const value = parseFloat((bytes / Math.pow(1024, index)).toFixed(dm));
    return `${value} ${sizes[index]}`;
}

//start support
const supportButton = document.getElementsByClassName('support-button')[0];

if ($("#user-authorized").length === 0  || $("#user-authorized").val() === 'false') {
    supportButton.addEventListener('click', function(event) {
        event.preventDefault();
        location.replace('../signin');
    });
} else {
    const chatBox = document.getElementsByClassName('chat-box')[0];
    const supportHide = document.getElementById('support-hide');
    const photo = document.getElementById('support_photo_input');

    const linkPhotoSvg = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" focusable="false" viewBox="0 0 16 16" data-garden-id="buttons.icon" data-garden-version="8.76.7" class="StyledIcon-sc-19meqgg-0 cqORhS">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" d="M9.5 4v7.7c0 .8-.7 1.5-1.5 1.5s-1.5-.7-1.5-1.5V3C6.5 1.6 7.6.5 9 .5s2.5 1.1 2.5 2.5v9c0 1.9-1.6 3.5-3.5 3.5S4.5 13.9 4.5 12V4"></path>
                </svg>`;
    const deletePhotoSvg = `
            <svg width="24" height="24" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13.4671 3.78564L12.9989 11.7644C12.8793 13.8029 12.8194 14.8222 12.3344 15.555C12.0945 15.9173 11.7857 16.2231 11.4277 16.4529C10.7034 16.9177 9.73391 16.9177 7.79497 16.9177C5.85348 16.9177 4.88273 16.9177 4.15796 16.452C3.79964 16.2218 3.49076 15.9155 3.25102 15.5526C2.76609 14.8186 2.7076 13.7979 2.5906 11.7565L2.13379 3.78564" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                <path d="M14.6 3.78564H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                <path d="M10.8639 3.78558L10.3481 2.66476C10.0055 1.92024 9.83421 1.54798 9.53869 1.31581C9.47314 1.26431 9.40373 1.2185 9.33115 1.17883C9.00391 1 8.61117 1 7.82571 1C7.02051 1 6.61792 1 6.28525 1.18633C6.21152 1.22763 6.14116 1.27529 6.07491 1.32883C5.77597 1.57041 5.60898 1.95629 5.27501 2.72806L4.81738 3.78558" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                <path d="M5.91113 12.5405L5.91113 7.76525" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                <path d="M9.68848 12.5399L9.68848 7.76465" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
            </svg>`;

    document.addEventListener('click', () => {
        if (chatBox.style.display !== 'none' && !event.target.closest('.chat-box-meta') && !event.target.closest('.support-button')) {
            chatBox.style.display = 'none';
            supportButton.style.display = 'flex';
        }
    });

    supportHide.addEventListener('click', () => {
        chatBox.style.display = 'none';
        supportButton.style.display = 'flex';
    });

    supportButton.addEventListener('click', () => {
        updateSupport(true);
        chatBox.style.display = 'flex';
        supportButton.style.display = 'none';

        $(".support-unviewed").css('display', 'none');
    });

    function updateSupport(scroll) {
        $("#chat_messages").load("../api/user/support/get", function(responseText) {
            var regex = /\bhttps:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/gi;
            var wrappedText = responseText.replace(regex, function(match) {
                return '<a style="text-decoration-style: bold; font-weight: bold; color: mediumblue; text-decoration-line: underline;" rel="noreferrer" target="_blank" href="' + match + '">' + match + '</a>';
            });
            $("#chat_messages").html(wrappedText);

            if (scroll) {
                var chatMessages = document.getElementById('chat_messages');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    }

    $("#support_send").on('click', function(event) {
        event.preventDefault();

        const btn = $(this);
        if (btn.attr('disabled') === 'disabled') {
            return;
        }

        const message = $("#chat_input").val();
        const image = photo && photo.files && photo.files[0] ? photo.files[0] : 'undefined';

        if (image === 'undefined' && (message === " " || message.length < 1)) {
            noti(getMessage('support.message.is.empty'),  "error");
            return;
        }

        if (message && message.length > 2000) {
            noti(getMessage('support.message.length.limit'),  "error");
            return;
        }

        btn.addClass('send-disabled');
        btn.attr('disabled', 'disabled');

        const formData = new FormData();

        if (message && message !== " " && message.length > 0) {
            formData.append("message", message);
        }

        if (image !== 'undefined') {
            formData.append("image", image);
            resetFileUpload();
        }

        $.ajax({
            url: '../api/user/support/send',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function (response) {
                btn.removeClass('send-disabled');
                btn.removeAttr('disabled');

                if (response === 'success') {
                    $("#chat_input").val('');
                    updateSupport();
                    setTimeout(function() {
                        var chatMessages = document.getElementById('chat_messages');
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 300);
                    return;
                }

                if (response === 'support_ban') {
                    errorPopup('SUPPORT');
                    return;
                }

                noti(getMessage(response), 'error');
            }
        });
    });

    var uploadStatus = false;

    $("#support_link_photo").on('click', function(event) {
        if (uploadStatus) {
            resetFileUpload();
        } else {
            $("#support_photo_input").click();
        }
    });

    $("#support_photo_input").on('change', function(event) {
        if (!this.files || this.files.length === 0) {
            return;
        }

        const photoSize = this.files[0]['size'];

        if (photoSize > 10000000) {
            noti(getMessage('support.limit.image.size'), "error");
            event.preventDefault();
            return;
        }

        $("#support_link_photo").html(deletePhotoSvg);
        const span = $("#support_photo_size");
        span.css('display', 'block');
        span.text(formatBytes(photoSize, 2));
        uploadStatus = true;
    });

    $("#support_photo_input").on('click', function(event) {
        if (photo && photo.files && photo.files.length > 0) {
            noti(getMessage('support.limit.images.count'), "error");
            event.preventDefault();
        }
    });

    function resetFileUpload() {
        const input = $("#support_photo_input");
        input.wrap('<form>').closest('form').get(0).reset();
        input.unwrap();
        const span = $("#support_photo_size");
        span.css('display', 'none');
        span.text('');
        $("#support_link_photo").html(linkPhotoSvg);
        uploadStatus = false;
    }

    setInterval(() => {
        if (chatBox.style.display !== 'none') {
            updateSupport();
        }

        $.ajax({
            url: "../api/user/profile",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                action: "GET_UPDATES"
            }),
            success: function (response) {
                const json = JSON.parse(response);

                const supportUnviewed = parseInt(json['support_unviewed']);

                if (chatBox.style.display === 'none' && supportUnviewed > 0) {
                    $(".support-unviewed").html('' + supportUnviewed);
                    $(".support-unviewed").css('display', '');
                }

                if (json['alert']) {
                    const type = json['alert']['type'];
                    const message = json['alert']['message'];

                    if (type === 'NOTIFICATION') {
                        noti(message, 'success');
                    } else if (type === 'ALERT') {
                        popup(getMessage('popup.alert.info.title'), message, '../assets/img/info.svg', 'success', true);
                    } else if (type === 'BONUS') {
                        popup(getMessage('popup.alert.bonus.title'), message, '../assets/img/gift.svg', 'success', true, function() {
                            location.reload();
                        });
                    }
                }
            }
        });
    }, 10000);
}
//end support