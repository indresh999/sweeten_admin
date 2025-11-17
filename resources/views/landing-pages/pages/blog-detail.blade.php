<x-app-layout layout="landing" :isHeader1=true>
    <x-landing-pages.widgets.sub-header subTitle="Blog Detail" subBreadcrume="Blog Detail" />
    <div class="inner-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <x-landing-pages.widgets.blog-deatail blogImage="blog/01.webp" cardClass="mt-4"
                        blogDate="December 26, 2022"
                        blogTitle="The Cheapest Destinations Of All Time, A List Of Beauty And Budget."
                        blogDescription="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Neque at velit ultrices convallis. Purus sed adipiscing hendrerit risus id dapibus tristique consectetur. Enim non viverra massa sollicitudin arcu aliquam, sagittis aliquet diam"
                        blogAuther="Travel" />
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Comment(3)</h5>
                        </div>
                        <div class="card-body">
                            <div class="card shadow-none bg-transparent border mb-3">
                                <div class="card-body">
                                    <div class="d-flex flex-sm-nowrap flex-wrap justify-content-center gap-3">
                                        <div>
                                            <img class="img-fluid object-contain avatar-120 rounded-0"
                                                src="{{asset('images/landing-pages/images/blog-avatar/02.webp')}}"
                                                alt="01" loading="lazy" />
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center my-2 my-lg-0">
                                                <h6 class=" ">Jackson Jones</h6>
                                                <a class="text-dark " href="#">Reply</a>
                                            </div>
                                            <small class="text-primary sub-title">March 01st 2021</small>
                                            <p class="mt-2 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing
                                                elit. Aliquam ut eu morbi tincidunt erat egestas quisque ultrices ut.
                                                Vel elementum blandit et tellus sit tincidunt.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow-none bg-transparent border ms-5">
                                <div class="card-body">
                                    <div class="d-flex flex-sm-nowrap flex-wrap justify-content-center gap-3">
                                        <div>
                                            <img class="img-fluid object-contain avatar-120 rounded-0"
                                                src="{{asset('images/landing-pages/images/blog-avatar/03.webp')}}"
                                                alt="01" loading="lazy" />
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center my-2 my-lg-0">
                                                <h6 class="">Lara Williams</h6>
                                                <a class="text-dark " href="#">Reply</a>
                                            </div>
                                            <small class="text-primary sub-title">March 13th 2021</small>
                                            <p class="mt-2 mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing
                                                elit. Aliquam ut eu morbi tincidunt erat egestas quisque ultrices ut.
                                                Vel elementum blandit et tellus sit tincidunt.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow-none bg-transparent border mb-0">
                                <div class="card-body">
                                    <div class="d-flex flex-sm-nowrap flex-wrap justify-content-center gap-3">
                                        <div>
                                            <img class="img-fluid object-contain avatar-120 rounded-0"
                                                src="{{asset('images/landing-pages/images/blog-avatar/02.webp')}}"
                                                alt="01" loading="lazy" />
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center my-2 my-lg-0">
                                                <h6 class="">Jackson Jones</h6>
                                                <a class="text-dark " href="#">Reply</a>
                                            </div>
                                            <small class="text-primary sub-title">March 20th 2021</small>
                                            <p class="mt-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                                Aliquam ut eu morbi tincidunt erat egestas quisque ultrices ut. Vel
                                                elementum blandit et tellus sit tincidunt.</p>
                                            <div class="d-flex mb-3">
                                                <a class="" href="#">Reply To Jackson Jones</a>
                                                <a class="text-body ms-3" href="#">Cancel Reply</a>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group ">
                                                    <input type="text" class="form-control"
                                                        placeholder=" Hi there, I love your blog ">
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <button type="submit" class="btn btn-primary rounded">Get
                                                    Started</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Post a Comment.</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="full-name" class="form-label">First Name</label>
                                            <input type="text" class="form-control w-100" id="full-name"
                                                placeholder=" John">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="last-name" class="form-label">Email ID</label>
                                            <input type="text" class="form-control w-100" id="last-name"
                                                placeholder="XYZ@exampleemail.com ">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="message" class="form-label">Enter your Comment</label>
                                            <input type="text" id="message" class="form-control w-100"
                                                placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 d-flex justify-content-between">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="customCheck1">
                                            <label class="form-check-label" for="customCheck1">Save my name and email in
                                                this browser for the next time I comment.</label>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Search</h5>
                            <div class="nav">
                                <div class="input-group search-input w-100">
                                    <span class="input-group-text" id="search-input">
                                        <svg class="icon-18" width="18" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            </circle>
                                            <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </svg>
                                    </span>
                                    <input type="search" class="form-control" placeholder="Search...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">About Me</h5>
                            <div class="d-flex align-items-center gap-3">
                                <img class="img-fluid rounded-circle avatar-130"
                                    src="{{asset('images/landing-pages/images/blog-avatar/01.webp')}}" alt="user-img">
                                <div>
                                    <p class="mb-3 text-primary fw-bold">Loren Banks</p>
                                    <p class="mt-3">Elit vitae neque velit mattis elementum egestas non, Sem eget.</p>
                                    <div class="d-flex gap-3">
                                        <a href="#">
                                            <svg class="icon-24" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M23.9998 12C23.9998 5.37234 18.6273 0 12.0007 0C5.37201 0 0 5.37218 0 12C0 18.6268 5.37217 24 12.0007 24C18.6275 24 23.9998 18.6268 23.9998 12Z"
                                                    fill="#3D83D9" />
                                                <path d="M9.06396 9.07227H6.28613V17.011H9.06396V9.07227Z"
                                                    fill="white" />
                                                <path
                                                    d="M7.67514 5.10254C6.80501 5.10254 6.12388 5.90215 6.32007 6.80286C6.43403 7.32607 6.86083 7.74668 7.38588 7.85212C8.28124 8.03193 9.06397 7.35414 9.06397 6.49153C9.06397 5.72568 8.44333 5.10254 7.67514 5.10254Z"
                                                    fill="white" />
                                                <path
                                                    d="M18.1938 11.511C18.0069 10.0148 17.2585 9.07227 15.2358 9.07227C13.8002 9.07227 13.2293 9.29619 12.9001 9.92284V9.07227H10.6514V17.011H12.9656V12.8554C12.9656 11.818 13.1622 11.0344 14.4449 11.0344C15.7092 11.0344 15.8108 11.9988 15.8108 12.9228V17.0112H18.1939C18.1938 17.0112 18.2379 11.8608 18.1938 11.511Z"
                                                    fill="white" />
                                            </svg>
                                        </a>
                                        <a href="#">
                                            <svg class="icon-24" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M24.0002 11.9992C24.0002 5.37225 18.6279 0 12 0C5.37242 0 0 5.37225 0 11.9992C0 18.6262 5.37242 24 12 24C18.6281 24 24.0002 18.6262 24.0002 11.9992Z"
                                                    fill="#395196" />
                                                <path
                                                    d="M13.0575 9.15703V8.02035C13.0575 7.46672 13.427 7.33737 13.6857 7.33737C13.9452 7.33737 15.2811 7.33737 15.2811 7.33737V4.90325L13.0846 4.89355C10.6466 4.89355 10.093 6.71004 10.093 7.87296V9.15703H8.68359V12.0004H10.1052C10.1052 15.2223 10.1052 19.1073 10.1052 19.1073H12.9477C12.9477 19.1073 12.9477 15.1827 12.9477 12.0004H15.0575L15.317 9.15703H13.0575V9.15703Z"
                                                    fill="white" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Categories</h5>
                            <ul class="list-inline list-main d-flex flex-column gap-4 mb-0">
                                <li class="">
                                    <div class="iq-blog-categories d-flex justify-content-between align-items-center">
                                        <h6 class="iq-categories-name mb-0">Beauty</h6>
                                        <p class="iq-categories-indicator line-around-2 mb-0">
                                            <span class="px-5"></span>
                                        </p>
                                        <span class="px-3 d-flex align-items-center">(8)</span>
                                    </div>
                                </li>
                                <li class="">
                                    <div class="iq-blog-categories d-flex justify-content-between align-items-center">
                                        <h6 class="iq-categories-name mb-0">SkinCare</h6>
                                        <p class="iq-categories-indicator line-around-2 mb-0">
                                            <span class="px-5"></span>
                                        </p>
                                        <span class="px-3 d-flex align-items-center">(2)</span>
                                    </div>
                                </li>
                                <li class="">
                                    <div class="iq-blog-categories d-flex justify-content-between align-items-center">
                                        <h6 class="iq-categories-name mb-0">HairCare</h6>
                                        <p class="iq-categories-indicator line-around-2 mb-0">
                                            <span class="px-5"></span>
                                        </p>
                                        <span class="px-3 d-flex align-items-center">(6)</span>
                                    </div>
                                </li>
                                <li class="">
                                    <div class="iq-blog-categories d-flex justify-content-between align-items-center">
                                        <h6 class="iq-categories-name mb-0">Makeup</h6>
                                        <p class="iq-categories-indicator line-around-2 mb-0">
                                            <span class="px-5"></span>
                                        </p>
                                        <span class="px-3 d-flex align-items-center">(6)</span>
                                    </div>
                                </li>
                                <li class="">
                                    <div class="iq-blog-categories d-flex justify-content-between align-items-center">
                                        <h6 class="iq-categories-name mb-0">Business</h6>
                                        <p class="iq-categories-indicator line-around-2 mb-0">
                                            <span class="px-5"></span>
                                        </p>
                                        <span class="px-3 d-flex align-items-center">(5)</span>
                                    </div>
                                </li>
                                <li class="">
                                    <div class="iq-blog-categories d-flex justify-content-between align-items-center">
                                        <h6 class="iq-categories-name mb-0">Salon</h6>
                                        <p class="iq-categories-indicator line-around-2 mb-0">
                                            <span class="px-5"></span>
                                        </p>
                                        <span class="px-3 d-flex align-items-center">(4)</span>
                                    </div>
                                </li>
                                <li class="">
                                    <div class="iq-blog-categories d-flex justify-content-between align-items-center">
                                        <h6 class="iq-categories-name mb-0">Toner</h6>
                                        <p class="iq-categories-indicator line-around-2 mb-0">
                                            <span class="px-5"></span>
                                        </p>
                                        <span class="px-3 d-flex align-items-center">(8)</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Recent Posts</h5>
                            <img class="img-fluid fit-img mb-4 object-cover iq-recent-post w-100"
                                src="{{asset('images/landing-pages/images/blog/03.webp')}}" alt="01" loading="lazy" />
                            <small class="text-primary sub-title mb-3">April 19th 2021</small>
                            <a href="#" class="iq-title">
                                <h6 class="mt-2 mb-3">5 Beauty Essentials Everyone Should Have in Their Collection.</h6>
                            </a>
                            <div class="d-flex gap-2">
                                <a href="#" class="text-body">Travel</a><span> | </span>
                                <a href="#" class="text-primary">Jenny Wilson</a>
                            </div>
                            <p class="pt-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ut eu morbi
                                tincidunt </p>
                            <button type="submit" class="btn btn-primary mt-2 rounded">Read More</button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">Popular Tags</h5>
                            <ul class="iq-col-masonry logik-blogtag list-unstyled gap-3">
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#Care</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#Beauty</span></a>
                                </li>
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#HairCare</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#SkinCare</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#Serum</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#Skin</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#Hydrate</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="bg-soft-primary rounded-pill iq-custom-badge">
                                        <span>#Radiant</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">Follow Us</h5>
                            <div class="d-grid gap-3 grid-cols-2">
                                <a href="#">
                                    <svg width="6" viewBox="0 0 5 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="2.5" cy="3" r="2.5" fill="currentcolor"></circle>
                                    </svg>
                                    <span class="text-body">Facebook</span></a>
                                <a href="#">
                                    <svg width="6" viewBox="0 0 5 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="2.5" cy="3" r="2.5" fill="currentcolor" />
                                    </svg>
                                    <span class="text-body">Instagram</span>
                                </a>
                                <a href="#">
                                    <svg width="6" viewBox="0 0 5 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="2.5" cy="3" r="2.5" fill="currentcolor"></circle>
                                    </svg>
                                    <span class="text-body">Twitter</span></a>
                                <a href="#">
                                    <svg width="6" viewBox="0 0 5 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="2.5" cy="3" r="2.5" fill="currentcolor" />
                                    </svg>
                                    <span class="text-body">Youtube</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>