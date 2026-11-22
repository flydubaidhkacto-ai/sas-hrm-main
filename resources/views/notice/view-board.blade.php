@php
    $isSlider = $slider ?? false;
@endphp

@if ($isSlider)
<div id="noticeSlider" class="notice-slider">
    <div class="notice-slider-viewport" id="noticeSliderViewport">
        <div class="notice-slider-slide active" id="noticeSliderSlide0">
            <div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>
        </div>
    </div>
    <div class="notice-slider-controls" id="noticeSliderControls">
        <button class="notice-slider-prev" id="noticeSliderPrev" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="notice-slider-next" id="noticeSliderNext" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <div class="notice-slider-dots" id="noticeSliderDots"></div>
</div>
<style>
    .notice-slider {
        position: relative;
        min-height: 240px;
        overflow: hidden;
    }
    .notice-slider-viewport {
        position: relative;
        width: 100%;
    }
    .notice-slider-slide {
        display: none;
        width: 100%;
    }
    .notice-slider-slide.active {
        display: block;
        animation: noticeFadeIn 0.45s ease-in-out;
    }
    @keyframes noticeFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .notice-slider-slide .card dash-widget {
        transition: all 0.3s ease;
    }
    .notice-slider-slide.latest-notice .card dash-widget {
        border-left: 4px solid #28a745;
        background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
    }
    .notice-slider-slide.latest-notice .notice-badge {
        display: inline-block;
    }
    .notice-slider-slide.past-notice .card dash-widget {
        border-left: 4px solid #ffc107;
        background: #fffdf5;
        opacity: 0.9;
    }
    .notice-slider-slide.past-notice .notice-past-label {
        display: inline-block;
    }
    .notice-badge {
        display: none;
        background: #28a745;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .notice-past-label {
        display: none;
        background: #ffc107;
        color: #333;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .notice-slider-slide h4 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.4;
    }
    .notice-slider-slide h4 a {
        color: #1071d6;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .notice-slider-slide h4 a:hover {
        color: #0d5bb5;
        text-decoration: underline;
    }
    .notice-slider-slide p {
        font-size: 13px;
        line-height: 1.6;
        color: #444;
        margin-bottom: 10px;
    }
    .notice-slider-slide .link {
        font-size: 12px;
        font-weight: 600;
    }
    .notice-slider-controls {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-50%);
        display: flex;
        justify-content: space-between;
        pointer-events: none;
        padding: 0 4px;
    }
    .notice-slider-prev,
    .notice-slider-next {
        pointer-events: auto;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background-color: rgba(0,0,0,0.25);
        color: #fff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        opacity: 0.8;
        transition: opacity 0.2s ease, background-color 0.2s ease;
        font-size: 10px;
    }
    .notice-slider-prev:hover,
    .notice-slider-next:hover {
        opacity: 1;
        background-color: rgba(0,0,0,0.45);
    }
    .notice-slider-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 12px;
    }
    .notice-slider-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: rgba(0,0,0,0.2);
        border: none;
        padding: 0;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .notice-slider-dot.active {
        background-color: #1071d6;
        transform: scale(1.2);
    }
    .notice-slider-dot:hover {
        background-color: rgba(0,0,0,0.4);
    }
    .notice-slider-slide .card dash-widget {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .notice-slider-slide.latest-notice .card dash-widget {
        border: 1px solid #28a745;
    }
    .notice-slider-slide.past-notice .card dash-widget {
        border: 1px solid #ffc107;
    }
</style>
@else
<div id="notice" class="notice">
</div>
@endif

<script>
    var exceptLimit = {{ $except_limit ?? 10000 }};
    @if ($isSlider)
    var noticeCurrentIndex = 0;
    var noticeTotalSlides = 0;
    var noticeAutoPlayTimer = null;
    var noticeAutoPlayPaused = false;

    function noticeGoToSlide(index) {
        const viewport = document.getElementById('noticeSliderViewport');
        const slides = viewport.querySelectorAll('.notice-slider-slide');
        const dots = document.querySelectorAll('.notice-slider-dot');
        if (!slides.length) return;

        noticeCurrentIndex = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === noticeCurrentIndex);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === noticeCurrentIndex);
        });
    }

    function noticeNextSlide() {
        const viewport = document.getElementById('noticeSliderViewport');
        const slides = viewport.querySelectorAll('.notice-slider-slide');
        noticeGoToSlide(noticeCurrentIndex + 1);
    }

    function noticePrevSlide() {
        const viewport = document.getElementById('noticeSliderViewport');
        const slides = viewport.querySelectorAll('.notice-slider-slide');
        noticeGoToSlide(noticeCurrentIndex - 1);
    }

    function noticeStartAutoPlay() {
        noticeStopAutoPlay();
        if (noticeTotalSlides <= 1) return;
        noticeAutoPlayTimer = setInterval(noticeNextSlide, 5000);
    }

    function noticeStopAutoPlay() {
        if (noticeAutoPlayTimer) {
            clearInterval(noticeAutoPlayTimer);
            noticeAutoPlayTimer = null;
        }
    }

    function noticeBindSliderEvents() {
        const slider = document.getElementById('noticeSlider');
        if (!slider) return;
        slider.addEventListener('mouseenter', () => {
            noticeAutoPlayPaused = true;
            noticeStopAutoPlay();
        });
        slider.addEventListener('mouseleave', () => {
            noticeAutoPlayPaused = false;
            noticeStartAutoPlay();
        });
        const prevBtn = document.getElementById('noticeSliderPrev');
        const nextBtn = document.getElementById('noticeSliderNext');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                noticePrevSlide();
                if (!noticeAutoPlayPaused) noticeStartAutoPlay();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                noticeNextSlide();
                if (!noticeAutoPlayPaused) noticeStartAutoPlay();
            });
        }
        const dots = document.querySelectorAll('.notice-slider-dot');
        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                noticeGoToSlide(i);
                if (!noticeAutoPlayPaused) noticeStartAutoPlay();
            });
        });
    }

    function noticeIsHolidayNotice(notice) {
        const text = ((notice.title || '') + ' ' + (notice.description || '')).toLowerCase();
        const holidayKeywords = ['holiday', 'circular', 'festival', 'vacation', 'public holiday', 'office closed', 'closed'];
        return holidayKeywords.some(keyword => text.includes(keyword));
    }

    function noticeIsPastNotice(notice) {
        if (!notice.created_at) return false;
        const created = new Date(notice.created_at);
        const now = new Date();
        const diffDays = (now - created) / (1000 * 60 * 60 * 24);
        return diffDays > 7;
    }
    @endif

    function markAsViewed(id) {
        @if ($isSlider)
        let viewport = document.getElementById('noticeSliderViewport');
        viewport.innerHTML = '<div class="notice-slider-slide active"><div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div></div>';
        document.getElementById('noticeSliderDots').innerHTML = '';
        @else
        let notice = $('.notice');
        notice.html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        @endif

        $.ajax({
            url: '{{ route('home') }}?notice=1&id=' + id + '&limit=' + {{ $limit ?? 3 }},
            type: 'GET',
            cache: false,
            data: {
                id: id,
                _token: '{{ csrf_token() }}',
                all: 1
            },
            success: function(data) {
                @if ($isSlider)
                if (!data || !Array.isArray(data) || data.length === 0) {
                    viewport.innerHTML = '<div class="notice-slider-slide active"><div class="text-center"><i>No notice found!</i></div></div>';
                    document.getElementById('noticeSliderDots').innerHTML = '';
                    $('.notice-all').addClass('d-none');
                    return;
                }

                let slidesHtml = '';
                let dotsHtml = '';

                data.forEach((notice, index) => {
                    const isLatest = index === 0;
                    const isHoliday = noticeIsHolidayNotice(notice);
                    const isPast = noticeIsPastNotice(notice);
                    let slideClass = 'notice-slider-slide';
                    if (isLatest) slideClass += ' latest-notice';
                    if (isHoliday && isPast) slideClass += ' past-notice';

                    const badgeHtml = isLatest ? '<span class="notice-badge">LATEST</span>' : '';
                    const pastLabelHtml = (isHoliday && isPast) ? '<span class="notice-past-label">PAST</span>' : '';

                    slidesHtml += `
                        <div class="${slideClass} ${index === 0 ? 'active' : ''}" id="noticeSliderSlide${index}">
                            <div class="card dash-widget">
                                <div class="row" style="min-height: 150px; padding: 10px 10px 0px 10px;">
                                    <div class="col-md-12" style="text-align: left">
                                        ${notice.notViewed ?
                                        `<button id="close-notice" class="btn btn-light position-absolute" data-toggle="tooltip" data-placement="top" title="Mark as viewed"
                                            style=" right: 1%;" onclick="markAsViewed(${notice.id})">
                                            <i class="fas fa-times"></i>
                                        </button>`
                                        : ''}
                                        ${badgeHtml}
                                        ${pastLabelHtml}
                                        <h4>
                                            <a href="${notice.link}" target="_blank">
                                                ${notice.title}
                                            </a>
                                        </h4>
                                         <p>
                                              ${(notice.description || '').substring(0, exceptLimit) + (notice.description && notice.description.length > exceptLimit ? '...' : '')}
                                         </p>
                                         <small class="text-muted" style="font-size: 11px;">
                                             <i class="fa fa-eye"></i> <span class="notice-viewers-btn" style="cursor: pointer; text-decoration: underline; color: #1071d6;" data-notice-id="${notice.id}" data-notice-title="${notice.title}">${notice.user_view_count || 0}/${notice.total_users || 0} employees seen</span>
                                         </small>
                                         ${
                                             (notice.attachments && notice.attachments.length) ? notice.attachments.map(function (item){
                                                let html = '<div class="mb-1">';
                                                if (item.is_pdf) {
                                                    html += `<button type="button" class="btn btn-sm btn-info pdf-preview-btn" data-url="${ item.url }" data-name="${ item.type }"><i class="fa fa-eye"></i> Preview PDF</button> `;
                                                }
                                                html += `<a class="link" href="/uploaded_files/${ item.path }" download="${ item.file_name }"><i class="fa fa fa-download"></i> ${ item.type }</a>`;
                                                html += '</div>';
                                                return html;
                                            }).join('') : ''
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    dotsHtml += `<button class="notice-slider-dot ${index === 0 ? 'active' : ''}" data-index="${index}" aria-label="Slide ${index + 1}"></button>`;
                });

                viewport.innerHTML = slidesHtml;
                document.getElementById('noticeSliderDots').innerHTML = dotsHtml;
                $('.notice-all').removeClass('d-none');

                noticeTotalSlides = data.length;
                noticeCurrentIndex = 0;
                noticeBindSliderEvents();
                noticeStartAutoPlay();
                @else
                if (!data || !Array.isArray(data) || data.length === 0) {
                    notice.html('<div class="text-center"><i>No notice found!</i></div>');
                    return console.log('No notice found!', data);
                }

                let noticeHtml = '';

                data.forEach(notice => {
                    noticeHtml += `
                        <div class="card dash-widget">
                            <div class="row" style="min-height: 150px; padding: 10px 10px 0px 10px;">
                                <div class="col-md-12" style="text-align: left">
                                    ${notice.notViewed ?
                                    `<button id="close-notice" class="btn btn-light position-absolute" data-toggle="tooltip" data-placement="top" title="Mark as viewed"
                                        style=" right: 1%;" onclick="markAsViewed(${notice.id})">
                                        <i class="fas fa-times"></i>
                                    </button>`
                                    : ''}
                                    <h4>
                                        <a href="${notice.link}" target="_blank">
                                            ${notice.title}
                                        </a>
                                    </h4>
                                     <p>
                                          ${(notice.description || '').substring(0, exceptLimit) + (notice.description && notice.description.length > exceptLimit ? '...' : '')}
                                     </p>
                                     <small class="text-muted" style="font-size: 11px;">
                                         <i class="fa fa-eye"></i> <span class="notice-viewers-btn" style="cursor: pointer; text-decoration: underline; color: #1071d6;" data-notice-id="${notice.id}" data-notice-title="${notice.title}">${notice.user_view_count || 0}/${notice.total_users || 0} employees seen</span>
                                     </small>
                                     ${
                                         (notice.attachments && notice.attachments.length) ? notice.attachments.map(function (item){
                                            let html = '<div class="mb-1">';
                                            if (item.is_pdf) {
                                                html += `<button type="button" class="btn btn-sm btn-info pdf-preview-btn" data-url="${ item.url }" data-name="${ item.type }"><i class="fa fa-eye"></i> Preview PDF</button> `;
                                            }
                                            html += `<a class="link" href="/uploaded_files/${ item.path }" download="${ item.file_name }"><i class="fa fa-download"></i> ${ item.type }</a>`;
                                            html += '</div>';
                                            return html;
                                        }).join('') : ''
                                    }
                                </div>
                            </div>
                        </div>
                    `;
                });

                notice.html(noticeHtml);
                $('.notice-all').removeClass('d-none');
                @endif
            },
            error: function(xhr, status, error) {
                @if ($isSlider)
                viewport.innerHTML = '<div class="notice-slider-slide active"><div class="text-center"><i>No notice found!</i></div></div>';
                document.getElementById('noticeSliderDots').innerHTML = '';
                @else
                $('.notice').html('<div class="text-center"><i>No notice found!</i></div>');
                @endif
                console.log('Error fetching notices:', error);
            }
        })
    }

    // Direct call instead of $(document).ready() for reliable execution
    // during jQuery replaceWith() script evaluation
    if (typeof markAsViewed === 'function') {
        markAsViewed(0);
    } else {
        setTimeout(function() { markAsViewed(0); }, 100);
    }
</script>
@if ($isSlider)
@include('notice.partials.notice-viewers-modal')
@endif
