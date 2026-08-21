@extends('layouts.master')

@section('page_title', 'Landing Page & SaaS CMS Management')

@section('content')
<div class="card">
    <div class="card-header header-elements-inline d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title font-weight-bold mb-0 text-info d-flex align-items-center">
                <i class="material-symbols-rounded mr-2">dashboard_customize</i>
                Landing Page &amp; SaaS CMS Dynamic Management
            </h5>
            <small class="text-muted">Manage all public landing page copy, hero badges, feature cards, pricing tiers, FAQs, and testimonials in real-time.</small>
        </div>
        <div class="header-elements d-flex align-items-center">
            <a href="{{ route('saas.landing') }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill mr-2 font-weight-semibold">
                <i class="material-symbols-rounded align-middle mr-1 font-size-xs">open_in_new</i> View Live Landing Page
            </a>
            <form action="{{ route('saas.admin.landing_cms.reset') }}" method="POST" onsubmit="return confirm('Reset all landing page contents to system defaults?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill font-weight-semibold">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-xs">restart_alt</i> Restore Defaults
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        {{-- Multi-Tab Navigation --}}
        <ul class="nav nav-tabs nav-tabs-highlight nav-justified mb-4">
            <li class="nav-item">
                <a href="#tab-hero" class="nav-link active font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-sm">rocket_launch</i> Hero &amp; Stats
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-features" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-sm">featured_play_list</i> Features &amp; Pillars
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-plans" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-sm">payments</i> Pricing Plans
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-comparison" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-sm">compare_arrows</i> Comparison Matrix
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-testimonials" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-sm">reviews</i> Testimonials
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-faqs" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-sm">quiz</i> FAQ Accordion
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-contact" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-sm">contact_support</i> Contact &amp; Footer
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: HERO & STATS --}}
            <div class="tab-pane fade show active" id="tab-hero">
                <form action="{{ route('saas.admin.landing_cms.hero') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="card-title font-weight-bold mb-0 text-dark">Hero Banner &amp; Headings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-semibold">Top Pill Badge Text:</label>
                                        <input type="text" class="form-control" name="hero_badge" value="{{ $cms['hero_badge'] ?? '' }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-semibold">Main Hero Headline (H1):</label>
                                        <input type="text" class="form-control" name="hero_title" value="{{ $cms['hero_title'] ?? '' }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-semibold">Hero Subtitle Paragraph:</label>
                                        <textarea class="form-control" name="hero_subtitle" rows="3" required>{{ $cms['hero_subtitle'] ?? '' }}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-semibold">Primary CTA Button Text:</label>
                                            <input type="text" class="form-control" name="hero_cta_text" value="{{ $cms['hero_cta_text'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-semibold">Primary CTA Sub-label:</label>
                                            <input type="text" class="form-control" name="hero_cta_sub" value="{{ $cms['hero_cta_sub'] ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold">Secondary Demo Button Text:</label>
                                        <input type="text" class="form-control" name="demo_cta_text" value="{{ $cms['demo_cta_text'] ?? '' }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="card-title font-weight-bold mb-0 text-dark">Statistics &amp; Trust Counters</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-semibold">Active Schools Metric:</label>
                                        <input type="text" class="form-control" name="stat_schools" value="{{ $cms['stat_schools'] ?? '150+' }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-semibold">Enrolled Students Metric:</label>
                                        <input type="text" class="form-control" name="stat_students" value="{{ $cms['stat_students'] ?? '45,000+' }}" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold">Uptime SLA Metric:</label>
                                        <input type="text" class="form-control" name="stat_uptime" value="{{ $cms['stat_uptime'] ?? '99.9%' }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-sm">save</i> Save Hero Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- TAB 2: FEATURES & PILLARS --}}
            <div class="tab-pane fade" id="tab-features">
                <form action="{{ route('saas.admin.landing_cms.features') }}" method="POST">
                    @csrf
                    <p class="text-muted mb-3">Manage the core feature pillars showcased on the public landing and features pages.</p>
                    
                    <div class="row">
                        @foreach ($cms['features'] ?? [] as $idx => $feat)
                            <div class="col-md-6 mb-3">
                                <div class="card border h-100">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <span class="font-weight-bold text-dark">Feature #{{ $idx + 1 }}</span>
                                        <span class="badge badge-info">{{ $feat['icon'] ?? 'star' }}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Feature Title:</label>
                                            <input type="text" class="form-control" name="features[{{ $idx }}][title]" value="{{ $feat['title'] ?? '' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Description:</label>
                                            <textarea class="form-control" name="features[{{ $idx }}][desc]" rows="2" required>{{ $feat['desc'] ?? '' }}</textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 form-group mb-0">
                                                <label class="font-weight-semibold">Material Icon Name:</label>
                                                <input type="text" class="form-control" name="features[{{ $idx }}][icon]" value="{{ $feat['icon'] ?? 'star' }}">
                                            </div>
                                            <div class="col-6 form-group mb-0">
                                                <label class="font-weight-semibold">Color Accent:</label>
                                                <select class="form-control" name="features[{{ $idx }}][color]">
                                                    <option value="info" {{ ($feat['color'] ?? '') === 'info' ? 'selected' : '' }}>Cyan / Info</option>
                                                    <option value="success" {{ ($feat['color'] ?? '') === 'success' ? 'selected' : '' }}>Green / Success</option>
                                                    <option value="warning" {{ ($feat['color'] ?? '') === 'warning' ? 'selected' : '' }}>Orange / Warning</option>
                                                    <option value="primary" {{ ($feat['color'] ?? '') === 'primary' ? 'selected' : '' }}>Blue / Primary</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-sm">save</i> Save Feature Pillars
                        </button>
                    </div>
                </form>
            </div>

            {{-- TAB 3: PRICING PLANS --}}
            <div class="tab-pane fade" id="tab-plans">
                <p class="text-muted mb-3">Manage subscription pricing tiers, termly/yearly rates, and student capacity limits.</p>
                <div class="row">
                    @foreach ($plans as $plan)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border h-100 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="font-weight-bold mb-0 text-dark">{{ $plan->name }} (<code>{{ $plan->slug }}</code>)</h6>
                                    @if($plan->active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('saas.admin.landing_cms.plan', ['plan_id' => $plan->id]) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Plan Display Name:</label>
                                            <input type="text" class="form-control" name="name" value="{{ $plan->name }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Termly Price (&#8358; / Term):</label>
                                            <input type="number" step="100" class="form-control font-weight-bold text-info" name="termly_price" value="{{ $plan->termly_price }}" required>
                                            <small class="text-muted">Billed every 4 months (3 terms per session)</small>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Yearly Price (&#8358; / Year):</label>
                                            <input type="number" step="100" class="form-control font-weight-bold text-success" name="yearly_price" value="{{ $plan->yearly_price }}" required>
                                            <small class="text-muted">Annual subscription with ~20% discount</small>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 form-group">
                                                <label class="font-weight-semibold">Max Students:</label>
                                                <input type="number" class="form-control" name="max_students" value="{{ $plan->max_students }}" required>
                                            </div>
                                            <div class="col-6 form-group">
                                                <label class="font-weight-semibold">Max Staff:</label>
                                                <input type="number" class="form-control" name="max_staff" value="{{ $plan->max_staff }}" required>
                                            </div>
                                        </div>
                                        <div class="form-group form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="planActiveCheck{{ $plan->id }}" name="active" value="1" {{ $plan->active ? 'checked' : '' }}>
                                            <label class="form-check-label font-weight-semibold" for="planActiveCheck{{ $plan->id }}">Active for Public Registration</label>
                                        </div>
                                        <button type="submit" class="btn btn-outline-primary btn-block rounded-pill font-weight-bold">
                                            Update {{ $plan->name }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- TAB 4: COMPARISON MATRIX --}}
            <div class="tab-pane fade" id="tab-comparison">
                <form action="{{ route('saas.admin.landing_cms.comparison') }}" method="POST">
                    @csrf
                    <p class="text-muted mb-3">Manage the comparative table points comparing traditional software vs. Digiskul Cloud ERP.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="comparisonTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 25%;">Feature / Dimension</th>
                                    <th style="width: 35%;">Traditional School Systems</th>
                                    <th style="width: 35%;">Digiskul Cloud ERP</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cms['comparison'] ?? [] as $cIdx => $comp)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" name="comparison[{{ $cIdx }}][feature]" value="{{ $comp['feature'] ?? '' }}" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="comparison[{{ $cIdx }}][traditional]" value="{{ $comp['traditional'] ?? '' }}" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-success font-weight-bold" name="comparison[{{ $cIdx }}][digiskul]" value="{{ $comp['digiskul'] ?? '' }}" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove();">
                                                <i class="material-symbols-rounded font-size-xs">delete</i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-outline-info rounded-pill font-weight-semibold" onclick="addComparisonRow()">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-xs">add</i> Add Comparison Point
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-sm">save</i> Save Comparison Matrix
                        </button>
                    </div>
                </form>
            </div>

            {{-- TAB 5: TESTIMONIALS --}}
            <div class="tab-pane fade" id="tab-testimonials">
                <form action="{{ route('saas.admin.landing_cms.testimonials') }}" method="POST">
                    @csrf
                    <p class="text-muted mb-3">Manage testimonials and reviews from school administrators and proprietors.</p>

                    <div class="row" id="testimonialsContainer">
                        @foreach ($cms['testimonials'] ?? [] as $tIdx => $t)
                            <div class="col-md-4 mb-3 testimonial-card-item">
                                <div class="card border h-100">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <span class="font-weight-bold text-dark">Review #{{ $tIdx + 1 }}</span>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="$(this).closest('.testimonial-card-item').remove();">
                                            <i class="material-symbols-rounded font-size-xs">delete</i> Remove
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Author Name:</label>
                                            <input type="text" class="form-control" name="testimonials[{{ $tIdx }}][name]" value="{{ $t['name'] ?? '' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Role &amp; School:</label>
                                            <input type="text" class="form-control" name="testimonials[{{ $tIdx }}][role]" value="{{ $t['role'] ?? '' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-semibold">Quote Text:</label>
                                            <textarea class="form-control" name="testimonials[{{ $tIdx }}][quote]" rows="3" required>{{ $t['quote'] ?? '' }}</textarea>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="font-weight-semibold">Rating (1 to 5 Stars):</label>
                                            <select class="form-control" name="testimonials[{{ $tIdx }}][rating]">
                                                <option value="5" {{ ($t['rating'] ?? 5) == 5 ? 'selected' : '' }}>5 Stars</option>
                                                <option value="4" {{ ($t['rating'] ?? 5) == 4 ? 'selected' : '' }}>4 Stars</option>
                                                <option value="3" {{ ($t['rating'] ?? 5) == 3 ? 'selected' : '' }}>3 Stars</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-outline-info rounded-pill font-weight-semibold" onclick="addTestimonialCard()">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-xs">add</i> Add Testimonial
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-sm">save</i> Save Testimonials
                        </button>
                    </div>
                </form>
            </div>

            {{-- TAB 6: FAQS --}}
            <div class="tab-pane fade" id="tab-faqs">
                <form action="{{ route('saas.admin.landing_cms.faqs') }}" method="POST">
                    @csrf
                    <p class="text-muted mb-3">Manage questions and answers displayed in the public FAQ accordion.</p>

                    <div id="faqsContainer">
                        @foreach ($cms['faqs'] ?? [] as $fIdx => $faq)
                            <div class="card border mb-3 faq-item-card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <span class="font-weight-bold text-dark">FAQ #{{ $fIdx + 1 }}</span>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="$(this).closest('.faq-item-card').remove();">
                                        <i class="material-symbols-rounded font-size-xs">delete</i> Remove
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-semibold">Question:</label>
                                        <input type="text" class="form-control" name="faqs[{{ $fIdx }}][q]" value="{{ $faq['q'] ?? '' }}" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold">Answer:</label>
                                        <textarea class="form-control" name="faqs[{{ $fIdx }}][a]" rows="2" required>{{ $faq['a'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-outline-info rounded-pill font-weight-semibold" onclick="addFaqCard()">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-xs">add</i> Add FAQ Item
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-sm">save</i> Save FAQs
                        </button>
                    </div>
                </form>
            </div>

            {{-- TAB 7: CONTACT & FOOTER --}}
            <div class="tab-pane fade" id="tab-contact">
                <form action="{{ route('saas.admin.landing_cms.contact') }}" method="POST">
                    @csrf
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="card-title font-weight-bold mb-0 text-dark">Public Contact Channels &amp; Copyright Notice</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Support Email Address:</label>
                                    <input type="email" class="form-control" name="contact_email" value="{{ $cms['contact_email'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Support Phone Number:</label>
                                    <input type="text" class="form-control" name="contact_phone" value="{{ $cms['contact_phone'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">WhatsApp Support Contact:</label>
                                    <input type="text" class="form-control" name="contact_whatsapp" value="{{ $cms['contact_whatsapp'] ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Footer Copyright Line:</label>
                                    <input type="text" class="form-control" name="footer_copyright" value="{{ $cms['footer_copyright'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-semibold">Physical Head Office Address:</label>
                                <textarea class="form-control" name="contact_address" rows="2" required>{{ $cms['contact_address'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                            <i class="material-symbols-rounded align-middle mr-1 font-size-sm">save</i> Save Contact &amp; Footer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let compCount = {{ count($cms['comparison'] ?? []) }};
    let testCount = {{ count($cms['testimonials'] ?? []) }};
    let faqCount = {{ count($cms['faqs'] ?? []) }};

    function addComparisonRow() {
        compCount++;
        const row = `<tr>
            <td><input type="text" class="form-control" name="comparison[${compCount}][feature]" placeholder="Feature Name" required></td>
            <td><input type="text" class="form-control" name="comparison[${compCount}][traditional]" placeholder="Traditional system limitation" required></td>
            <td><input type="text" class="form-control text-success font-weight-bold" name="comparison[${compCount}][digiskul]" placeholder="Digiskul cloud ERP capability" required></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove();"><i class="material-symbols-rounded font-size-xs">delete</i></button></td>
        </tr>`;
        $('#comparisonTable tbody').append(row);
    }

    function addTestimonialCard() {
        testCount++;
        const card = `<div class="col-md-4 mb-3 testimonial-card-item">
            <div class="card border h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <span class="font-weight-bold text-dark">New Review</span>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="$(this).closest('.testimonial-card-item').remove();"><i class="material-symbols-rounded font-size-xs">delete</i> Remove</button>
                </div>
                <div class="card-body">
                    <div class="form-group"><label class="font-weight-semibold">Author Name:</label><input type="text" class="form-control" name="testimonials[${testCount}][name]" placeholder="e.g. Dr. A. Bello" required></div>
                    <div class="form-group"><label class="font-weight-semibold">Role &amp; School:</label><input type="text" class="form-control" name="testimonials[${testCount}][role]" placeholder="e.g. Principal, Model High School" required></div>
                    <div class="form-group"><label class="font-weight-semibold">Quote Text:</label><textarea class="form-control" name="testimonials[${testCount}][quote]" rows="3" placeholder="Testimonial quote text..." required></textarea></div>
                    <div class="form-group mb-0"><label class="font-weight-semibold">Rating:</label><select class="form-control" name="testimonials[${testCount}][rating]"><option value="5">5 Stars</option><option value="4">4 Stars</option></select></div>
                </div>
            </div>
        </div>`;
        $('#testimonialsContainer').append(card);
    }

    function addFaqCard() {
        faqCount++;
        const card = `<div class="card border mb-3 faq-item-card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="font-weight-bold text-dark">New FAQ</span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="$(this).closest('.faq-item-card').remove();"><i class="material-symbols-rounded font-size-xs">delete</i> Remove</button>
            </div>
            <div class="card-body">
                <div class="form-group"><label class="font-weight-semibold">Question:</label><input type="text" class="form-control" name="faqs[${faqCount}][q]" placeholder="FAQ Question..." required></div>
                <div class="form-group mb-0"><label class="font-weight-semibold">Answer:</label><textarea class="form-control" name="faqs[${faqCount}][a]" rows="2" placeholder="FAQ Answer..." required></textarea></div>
            </div>
        </div>`;
        $('#faqsContainer').append(card);
    }
</script>
@endsection
