<?php
$workshops = [
    [
        "id" => 1,
        "title" => "Hobby Class",
        "description" => "A light, one-day class for beginners and enthusiasts. Each date features different floral techniques — from bouquets and baskets to centerpieces and flower boxes. Choose your preferred session and bring home your own handmade creation.",
        "schedule" => [
            [ "date" => "2025-08-30", "time" => "8:00 AM - 12:00 PM", "content" => "Mix Flower Bouquet & Flower Basket", "venue" => "Trevi Cafe, Kuching" ],
            [ "date" => "2025-09-13", "time" => "1:00 PM - 5:00 PM", "content" => "Centerpiece Arrangement & Mix Flower Bouquet", "venue" => "Biddy's, Kuching" ],
            [ "date" => "2025-10-18", "time" => "2:00 PM - 6:00 PM", "content" => "Mix Flower Bouquet & Flower Box", "venue" => "Biddy's, Kuching" ],
            [ "date" => "2025-11-29", "time" => "8:00 AM - 12:00 PM", "content" => "Mix Flower Bouquet & Centerpiece Arrangement", "venue" => "Trevi Cafe, Kuching" ],
            [ "date" => "2025-12-20", "time" => "8:00 AM - 12:00 PM", "content" => "Mix Flower Bouquet & Flower Basket", "venue" => "Trevi Cafe, Kuching" ],
            [ "date" => "2026-01-17", "time" => "1:00 PM - 5:00 PM", "content" => "Centerpiece Arrangement & Mix Flower Bouquet", "venue" => "Biddy's, Kuching" ]
        ],
        "price" => "RM120",
        "level" => "Beginners & Enthusiasts",
        "image" => "img/workshop/hobby_class.jpg"
    ],
    [
        "id" => 2,
        "title" => "Handtied Bouquet",
        "description" => "A two-day intensive designed for those who want to perfect their bouquet-making skills. Learn six different handtied bouquet styles with plenty of practice under expert guidance.",
        "schedule" => [
            [
                "days" => [
                    [ "date" => "2025-08-16", "time" => "2:00 PM - 5:30 PM", "content" => "Spiral Handtied-Round Layers, Single Stalk Bouquet & Korean Bouquet" ],
                    [ "date" => "2025-08-17", "time" => "8:00 AM - 12:00 PM", "content" => "Spiral Handtied-Classic Layers, Mix Flower Bouquet & Russian Bouquet" ]
                ], "venue" => "Biddy's, Kuching"
            ],
            [
                "days" => [
                    [ "date" => "2025-09-06", "time" => "1:00 PM - 5:00 PM", "content" => "Spiral Handtied-Round Layers, Mix Flower Bouquet & Korean Bouquet" ],
                    [ "date" => "2025-09-07", "time" => "8:00 AM - 11:30 AM", "content" => "Spiral Handtied-Classic Layers, Russian Bouquet & Single Stalk Bouquet" ]
                ], "venue" => "Trevi Cafe, Kuching"
            ],
            [
                "days" => [
                    [ "date" => "2025-09-20", "time" => "9:00 AM - 12:30 PM", "content" => "Spiral Handtied-Round Layers, Single Stalk Bouquet & Russian Bouquet" ],
                    [ "date" => "2025-09-21", "time" => "2:00 PM - 6:00 PM", "content" => "Spiral Handtied-Classic Layers, Mix Flower Bouquet & Korean Bouquet" ]
                ], "venue" => "Trevi Cafe, Kuching"
            ],
            [
                "days" => [
                    [ "date" => "2025-10-11", "time" => "2:00 PM - 6:00 PM", "content" => "Spiral Handtied-Round Layers, Mix Flower Bouquet & Russian Bouquet" ],
                    [ "date" => "2025-10-12", "time" => "8:00 AM - 11:30 AM", "content" => "Spiral Handtied-Classic Layers, Single Stalk Bouquet & Korean Bouquet" ]
                ], "venue" => "Biddy's, Kuching"
            ],
            [
                "days" => [
                    [ "date" => "2025-10-25", "time" => "9:00 AM - 12:30 PM", "content" => "Spiral Handtied-Classic Layers, Single Stalk Bouquet & Korean Bouquet" ],
                    [ "date" => "2025-10-26", "time" => "1:00 PM - 5:00 PM", "content" => "Mix Flower Bouquet, Spiral Handtied-Round Layers & Russian Bouquet" ]
                ], "venue" => "Trevi Cafe, Kuching"
            ],
            [
                "days" => [
                    [ "date" => "2025-11-15", "time" => "1:00 PM - 5:00 PM", "content" => "Spiral Handtied-Classic Layers, Mix Flower Bouquet & Russian Bouquet" ],
                    [ "date" => "2025-11-16", "time" => "2:00 PM - 5:30 PM", "content" => "Spiral Handtied-Round Layers, Single Stalk Bouquet & Korean Bouquet" ]
                ], "venue" => "Trevi Cafe, Kuching"
            ],
            [
                "days" => [
                    [ "date" => "2025-12-20", "time" => "9:00 AM - 12:30 PM", "content" => "Spiral Handtied-Round Layers, Single Stalk Bouquet & Russian Bouquet" ],
                    [ "date" => "2025-12-21", "time" => "2:00 PM - 6:00 PM", "content" => "Spiral Handtied-Classic Layers, Mix Flower Bouquet & Korean Bouquet" ]
                ], "venue" => "Trevi Cafe, Kuching"
            ],
            [
                "days" => [
                    [ "date" => "2026-01-03", "time" => "1:00 PM - 5:00 PM", "content" => "Spiral Handtied-Round Layers, Mix Flower Bouquet & Korean Bouquet" ],
                    [ "date" => "2026-01-04", "time" => "8:00 AM - 11:30 AM", "content" => "Spiral Handtied-Classic Layers, Russian Bouquet & Single Stalk Bouquet" ]
                ], "venue" => "Trevi Cafe, Kuching"
            ]
        ],
        "classes" => "5;&ensp;Approx. 1 - 1.5 hours per class",
        "price" => "RM180 per class (RM900 total)",
        "level" => "Intermediate",
        "image" => "img/workshop/handtied_bouquet.jpg"
    ],
    [
        "id" => 3,
        "title" => "Florist To Be 1",
        "description" => "A four-day florist training covering essential bouquet-making and arrangements, including handtied, bridal, table centerpieces, and basket bouquets.",
        "batches" => [  
            "August 2025" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-08-04", "2025-08-07", "2025-08-08"],
                        "content" => "Korean Bouquet, Boutineer & Single Stalk Bouquet"
                    ],
                    "2" => [
                        "dates" => ["2025-08-11", "2025-08-12", "2025-08-14"],
                        "content" => "Spiral Handtied-Round Layers & Russian Bouquet"
                    ],
                    "3" => [
                        "dates" => ["2025-08-19", "2025-08-20", "2025-08-21"],
                        "content" => "Mix Flower Bouquet, Flower Basket & Spiral Handtied-Classic Layers"
                    ],
                    "4" => [
                        "dates" => ["2025-08-25", "2025-08-27", "2025-08-28"],
                        "content" => "Bridal Bouquet, Centerpiece & Flower Stand"
                    ]
                ]
            ],
            "September 2025" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-09-02", "2025-09-04", "2025-09-05"],
                        "content" => "Spiral Handtied-Round Layers & Russian Bouquet"
                    ],
                    "2" => [
                        "dates" => ["2025-09-08", "2025-09-10", "2025-09-11"],
                        "content" => "Korean Bouquet, Boutineer & Single Stalk Bouquet"
                    ],
                    "3" => [
                        "dates" => ["2025-09-15", "2025-09-16", "2025-09-18"],
                        "content" => "Mix Flower Bouquet, Flower Basket & Spiral Handtied-Classic Layers"
                    ],
                    "4" => [
                        "dates" => ["2025-09-23", "2025-09-25", "2025-09-26"],
                        "content" => "Bridal Bouquet, Centerpiece & Flower Stand"
                    ]
                ]
            ],
            "October 2025" => [
                "venue" => "Trevi Cafe, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-10-02", "2025-10-08", "2025-10-09"],
                        "content" => "Korean Bouquet, Boutineer & Single Stalk Bouquet"
                    ],
                    "2" => [
                        "dates" => ["2025-10-13", "2025-10-15", "2025-10-17"],
                        "content" => "Spiral Handtied-Round Layers & Russian Bouquet"
                    ],
                    "3" => [
                        "dates" => ["2025-10-20", "2025-10-21", "2025-10-23"],
                        "content" => "Mix Flower Bouquet, Flower Basket & Spiral Handtied-Classic Layers"
                    ],
                    "4" => [
                        "dates" => ["2025-10-27", "2025-10-28", "2025-10-30"],
                        "content" => "Bridal Bouquet, Centerpiece & Flower Stand"
                    ]
                ]
            ],
            "November 2025" => [
                "venue" => "Trevi Cafe, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-11-03", "2025-11-05", "2025-11-06"],
                        "content" => "Korean Bouquet, Boutineer & Single Stalk Bouquet"
                    ],
                    "2" => [
                        "dates" => ["2025-11-11", "2025-11-12", "2025-11-14"],
                        "content" => "Mix Flower Bouquet, Flower Basket & Spiral Handtied-Classic Layers"
                    ],
                    "3" => [
                        "dates" => ["2025-11-18", "2025-11-19", "2025-11-20"],
                        "content" => "Spiral Handtied-Round Layers & Russian Bouquet"
                    ],
                    "4" => [
                        "dates" => ["2025-11-24", "2025-11-25", "2025-11-28"],
                        "content" => "Bridal Bouquet, Centerpiece & Flower Stand"
                    ]
                ]
            ],
            "December 2025" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-12-02", "2025-12-04", "2025-12-05"],
                        "content" => "Spiral Handtied-Round Layers & Russian Bouquet"
                    ],
                    "2" => [
                        "dates" => ["2025-12-08", "2025-12-10", "2025-12-11"],
                        "content" => "Korean Bouquet, Boutineer & Single Stalk Bouquet"
                    ],
                    "3" => [
                        "dates" => ["2025-12-15", "2025-12-16", "2025-12-18"],
                        "content" => "Mix Flower Bouquet, Flower Basket & Spiral Handtied-Classic Layers"
                    ],
                    "4" => [
                        "dates" => ["2025-12-22", "2025-12-23", "2025-12-26"],
                        "content" => "Bridal Bouquet, Centerpiece & Flower Stand"
                    ]
                ]
            ],
            "January 2026" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2026-01-05", "2026-01-07", "2026-01-08"],
                        "content" => "Korean Bouquet, Boutineer & Single Stalk Bouquet"
                    ],
                    "2" => [
                        "dates" => ["2026-01-13", "2026-01-14", "2026-01-15"],
                        "content" => "Spiral Handtied-Round Layers & Russian Bouquet"
                    ],
                    "3" => [
                        "dates" => ["2026-01-19", "2026-01-20", "2026-01-21"],
                        "content" => "Mix Flower Bouquet, Flower Basket & Spiral Handtied-Classic Layers"
                    ],
                    "4" => [
                        "dates" => ["2026-01-26", "2026-01-29", "2026-01-30"],
                        "content" => "Bridal Bouquet, Centerpiece & Flower Stand"
                    ]
                ]
            ]
        ],
        "time" => "8:30 AM - 12:30 PM / 2:30 PM - 6:30 PM",
        "classes" => "9;&ensp;Approx. 1.5 - 2 hours per class",
        "price" => "RM200 per class (RM1800 total)",
        "level" => "Advanced",
        "image" => "img/workshop/florist_to_be_1.jpg"
    ],
    [
        "id" => 4,
        "title" => "Florist To Be 2",
        "description" => "A four-day intensive for aspiring florists, featuring nine classes on advanced bouquets, floral boxes, mirror stands, and wedding arrangements to build practical experience.",
        "batches" => [  
            "August 2025" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-08-05", "2025-08-06"],
                        "content" => "Natural Design Bouquet, Boutineer & Flower Box"
                    ],
                    "2" => [
                        "dates" => ["2025-08-13", "2025-08-15"],
                        "content" => "Korean Bouquet & Spiral Handtied-Classic Layers"
                    ],
                    "3" => [
                        "dates" => ["2025-08-18", "2025-08-22"],
                        "content" => "Russian Bouquet & Mix Flower Bouquet"
                    ],
                    "4" => [
                        "dates" => ["2025-08-26", "2025-08-29"],
                        "content" => "Bridal Bouquet, Flower Basket & Mirror Flower Stand"
                    ]
                ]
            ],
            "September 2025" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-09-01", "2025-09-03"],
                        "content" => "Korean Bouquet & Spiral Handtied-Classic Layers"
                    ],
                    "2" => [
                        "dates" => ["2025-09-09", "2025-09-12"],
                        "content" => "Natural Design Bouquet, Boutineer & Flower Box"
                    ],
                    "3" => [
                        "dates" => ["2025-09-17", "2025-09-19"],
                        "content" => "Russian Bouquet & Mix Flower Bouquet"
                    ],
                    "4" => [
                        "dates" => ["2025-09-22", "2025-09-24"],
                        "content" => "Bridal Bouquet, Flower Basket & Mirror Flower Stand"
                    ]
                ]
            ],
            "October 2025" => [
                "venue" => "Trevi Cafe, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-10-03", "2025-10-06"],
                        "content" => "Natural Design Bouquet, Boutineer & Flower Box"
                    ],
                    "2" => [
                        "dates" => ["2025-10-10", "2025-10-14"],
                        "content" => "Russian Bouquet & Mix Flower Bouquet"
                    ],
                    "3" => [
                        "dates" => ["2025-10-22", "2025-10-24"],
                        "content" => "Korean Bouquet & Spiral Handtied-Classic Layers"
                    ],
                    "4" => [
                        "dates" => ["2025-10-29", "2025-10-31"],
                        "content" => "Bridal Bouquet, Flower Basket & Mirror Flower Stand"
                    ]
                ]
            ],
            "November 2025" => [
                "venue" => "Trevi Cafe, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-11-04", "2025-11-07"],
                        "content" => "Natural Design Bouquet, Boutineer & Flower Box"
                    ],
                    "2" => [
                        "dates" => ["2025-11-10", "2025-11-13"],
                        "content" => "Korean Bouquet & Spiral Handtied-Classic Layers"
                    ],
                    "3" => [
                        "dates" => ["2025-11-17", "2025-11-21"],
                        "content" => "Russian Bouquet & Mix Flower Bouquet"
                    ],
                    "4" => [
                        "dates" => ["2025-11-26", "2025-11-27"],
                        "content" => "Bridal Bouquet, Flower Basket & Mirror Flower Stand"
                    ]
                ]
            ],
            "December 2025" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2025-12-01", "2025-12-03"],
                        "content" => "Natural Design Bouquet, Boutineer & Flower Box"
                    ],
                    "2" => [
                        "dates" => ["2025-12-09", "2025-12-12"],
                        "content" => "Korean Bouquet & Spiral Handtied-Classic Layers"
                    ],
                    "3" => [
                        "dates" => ["2025-12-17", "2025-12-19"],
                        "content" => "Russian Bouquet & Mix Flower Bouquet"
                    ],
                    "4" => [
                        "dates" => ["2025-12-24", "2025-12-25"],
                        "content" => "Bridal Bouquet, Flower Basket & Mirror Flower Stand"
                    ]
                ]
            ],
            "January 2026" => [
                "venue" => "Biddy's, Kuching",
                "days" => [
                    "1" => [
                        "dates" => ["2026-01-06", "2026-01-09"],
                        "content" => "Korean Bouquet & Spiral Handtied-Classic Layers"
                    ],
                    "2" => [
                        "dates" => ["2026-01-12", "2026-01-16"],
                        "content" => "Natural Design Bouquet, Boutineer & Flower Box"
                    ],
                    "3" => [
                        "dates" => ["2026-01-22", "2026-01-23"],
                        "content" => "Russian Bouquet & Mix Flower Bouquet"
                    ],
                    "4" => [
                        "dates" => ["2026-01-27", "2026-01-28"],
                        "content" => "Bridal Bouquet, Flower Basket & Mirror Flower Stand"
                    ]
                ]
            ]
        ],
        "time" => "8:30 AM - 12:30 PM / 2:30 PM - 6:30 PM",
        "classes" => "9;&ensp;Approx. 1.5 - 2 hours per class",
        "price" => "RM195 per class (RM1755 total)",
        "level" => "Advanced",
        "image" => "img/workshop/florist_to_be_2.jpg"
    ]
];
?>