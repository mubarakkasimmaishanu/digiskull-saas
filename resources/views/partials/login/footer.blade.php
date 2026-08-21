<div class="navbar navbar-expand-lg login py-2 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap flex-shrink-0" style="min-height: 44px;">
    <span class="navbar-text d-flex align-items-center text-muted font-size-xs mb-0">
        <span>&copy; {{ date('Y') }} Digiskul. All rights reserved.</span>
    </span>

    <ul class="navbar-nav ml-auto d-flex flex-row align-items-center mb-0">
        <li class="nav-item mr-3">
            <a href="{{ route('privacy_policy') }}" class="text-muted hover-light font-size-xs d-flex align-items-center" target="_blank">
                <i class="material-symbols-rounded mr-1 font-size-xs">policy</i> <span>Privacy Policy</span>
            </a>
        </li>
        <li class="nav-item mr-3">
            <a href="{{ route('terms_of_use') }}" class="text-muted hover-light font-size-xs d-flex align-items-center" target="_blank">
                <i class="material-symbols-rounded mr-1 font-size-xs">gavel</i> <span>Terms of Use</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="text-muted hover-light font-size-xs d-flex align-items-center cursor-pointer" id="contact-link" data-toggle="modal" data-target="#admin-contact-modal" style="cursor: pointer;">
                <i class="material-symbols-rounded mr-1 font-size-xs">support_agent</i> <span>Contact Support</span>
            </a>
        </li>
    </ul>
</div>

<style>
    .hover-light:hover {
        color: #38bdf8 !important;
        text-decoration: none;
    }
</style>
