<x-app-layout layout="landing" :isHeader1=true>
    <x-landing-pages.widgets.sub-header subTitle="Features" subBreadcrume="Features" />
    <div class="section-card-padding bg-white">
        <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="mb-2 text-uppercase text-primary sub-title">
                    Features
                </div>
                <h2 class="heading-main-title">Features Provided <span class="text-primary">For You </span></h2>
            </div>
        </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
                <x-landing-pages.widgets.feature-section />
            </div>
        </div>
    </div>
    <div class="section-padding bg-body">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 text-center">
                <div class="mb-2 text-primary text-uppercase sub-title">Reviews</div>
                <h2 class="title-text text-secondary text-capitalize">What our <span
                        class="text-primary">Customer’s are saying</span></h2>
                </div>
                <div class="overflow-hidden slider-circle-btn mt-4" id="testimonial-one-slider">
                    <ul class="p-0 m-0 swiper-wrapper list-inline">
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="A true game changer."
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum
                        luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-1.webp" userTitle="Eleen Rogers" Id="01" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="Best you can Get"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum
                        luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-2.webp" userTitle="Brooklyn Simmons" Id="02" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one
                                testTitle="Perfect poduct for your
                        business"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget
                        condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-3.webp" userTitle="Jenny Wilson" Id="03" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="A true game changer."
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum
                        luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-1.webp" userTitle="Eleen Rogers" Id="01" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="Best you can Get"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum
                        luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-2.webp" userTitle="Brooklyn Simmons" Id="02" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one
                                testTitle="Perfect poduct for your
                        business"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget
                        condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-3.webp" userTitle="Jenny Wilson" Id="03" />
                        </li>
                    </ul>
                    <div class="swiper-button swiper-button-next"></div>
                    <div class="swiper-button swiper-button-prev"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="section-card-padding">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 text-center">
                    <p class="mb-3 text-primary">Reviews</p>
                    <h2 class="mb-5">What our <span class="text-primary">Customer’s are saying</span></h2>
                </div>
                <div class="overflow-hidden slider-circle-btn" id="testimonial-one-slider">
                    <ul class="p-0 m-0 swiper-wrapper list-inline">
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="A true game changer."
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-1.webp" userTitle="Eleen Rogers" Id="01" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="Best you can Get"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-2.webp" userTitle="Brooklyn Simmons" Id="02" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="Perfect poduct for your business"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-3.webp" userTitle="Jenny Wilson" Id="03" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="A true game changer."
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-1.webp" userTitle="Eleen Rogers" Id="01" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="Best you can Get"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-2.webp" userTitle="Brooklyn Simmons" Id="02" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden mb-0">
                            <x-landing-pages.widgets.testimonial-one testTitle="Perfect poduct for your business"
                                testText="“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vitae, eget condimentum luctus nec nec tellus sem sed. Diam elementum tellus posuere ipsum tortor.”"
                                testUser="user-3.webp" userTitle="Jenny Wilson" Id="03" />
                        </li>
                    </ul>
                    <div class="swiper-button swiper-button-next"></div>
                    <div class="swiper-button swiper-button-prev"></div>
                </div>
            </div>
        </div>
    </div> -->
    <div class="section-card-padding bg-white">
        <div class="container">
            <div class="row align-items-center">
            <div class="col-md-12 text-center">
                <div class="mb-2 text-uppercase text-primary sub-title">
                    Blog
                </div>
                <h2 class="text-secondary text-capitalize heading-main-title">All the <span class="text-primary">Support you
                        Need</span></h2>
            </div>
            <div class="overflow-hidden slider-circle-btn  app-slider" id="app-slider">
                    <ul class="p-0 m-0 swiper-wrapper list-inline">
                        <li class="swiper-slide card-slide card overflow-hidden">
                            <x-landing-pages.widgets.blog blogImage="home-1/04.webp"
                                blogTitle="The Cheapest Destinations Of All Time, A List Of Beauty And Budget." blogText="August 16,2023" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden">
                            <x-landing-pages.widgets.blog blogImage="home-1/05.webp"
                                blogTitle="Technology that unwinds your potential." blogText="August 17,2023" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden">
                            <x-landing-pages.widgets.blog blogImage="home-1/06.webp"
                                blogTitle="Generating the best online environment."  blogText="August 18,2023" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden">
                            <x-landing-pages.widgets.blog blogImage="home-1/04.webp"
                                blogTitle="The Cheapest Destinations Of All Time, A List Of Beauty And Budget." blogText="August 19,2023" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden">
                            <x-landing-pages.widgets.blog blogImage="home-1/05.webp"
                                blogTitle="Technology that unwinds your potential." blogText="August 20,2023" />
                        </li>
                        <li class="swiper-slide card-slide card overflow-hidden">
                            <x-landing-pages.widgets.blog blogImage="home-1/06.webp"
                                blogTitle="Generating the best online environment." blogText="August 21,2023" />
                        </li>
                    </ul>
                    <div class="swiper-button swiper-button-next"></div>
                    <div class="swiper-button swiper-button-prev"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
