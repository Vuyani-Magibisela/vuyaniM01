<?php
// Dummy data for blog posts
return [
    'posts' => [
        [
            'id' => 1,
            'title' => 'Getting Started with 3D Modeling for Beginners',
            'slug' => 'getting-started-with-3d-modeling',
            'excerpt' => 'Learn the basics of 3D modeling with this comprehensive guide for absolute beginners.',
            'content' => '<p>3D modeling is an exciting field that combines creativity with technical skills. Whether you\'re interested in creating game assets, product designs, architectural visualizations, or animated characters, the fundamentals remain the same.</p>

<h2>What is 3D Modeling?</h2>
<p>3D modeling is the process of creating a mathematical representation of any three-dimensional surface of an object. The product is called a 3D model. Someone who works with 3D models may be referred to as a 3D artist or a 3D modeler.</p>

<h2>Essential Software for Beginners</h2>
<p>There are several excellent software options for beginners:</p>
<ul>
    <li><strong>Blender</strong> - Free, open-source, and incredibly powerful</li>
    <li><strong>SketchUp</strong> - Intuitive and easy to learn, great for architectural modeling</li>
    <li><strong>TinkerCAD</strong> - Browser-based and perfect for absolute beginners</li>
    <li><strong>ZBrush Core</strong> - Simplified version of the industry-standard sculpting software</li>
</ul>

<h2>Basic Modeling Techniques</h2>
<p>When starting out, focus on mastering these fundamental techniques:</p>
<ol>
    <li><strong>Box Modeling</strong> - Starting with a primitive shape (like a cube) and refining it</li>
    <li><strong>Polygon Modeling</strong> - Building models one polygon at a time</li>
    <li><strong>Sculpting</strong> - Using digital clay to create organic shapes</li>
    <li><strong>Parametric Modeling</strong> - Using parameters to define the model</li>
</ol>

<h2>Understanding Topology</h2>
<p>Good topology refers to how the polygons flow over your 3D model. Clean topology is essential for animation, texturing, and overall model quality.</p>

<h2>Resources to Continue Learning</h2>
<p>Check out these resources to continue your 3D modeling journey:</p>
<ul>
    <li>Blender Guru\'s Donut Tutorial on YouTube</li>
    <li>CG Cookie courses</li>
    <li>Udemy\'s Complete Blender Creator course</li>
</ul>

<p>Remember, 3D modeling is a skill that takes time to master. Be patient with yourself and practice regularly. I\'ll be sharing more detailed tutorials on specific techniques in future articles, so stay tuned!</p>',
            'featured_image' => 'blog1.jpg',
            'category_id' => 1,
            'category_name' => 'Articles',
            'category_slug' => 'articles',
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'is_featured' => 1,
            'views' => 253,
            'published_at' => '2025-04-15 10:30:00',
            'created_at' => '2025-04-14 15:20:00',
            'updated_at' => '2025-04-15 11:45:00',
            'tags' => [
                ['name' => '3D Modeling', 'slug' => '3d-modeling'],
                ['name' => 'Beginners', 'slug' => 'beginners'],
                ['name' => 'Blender', 'slug' => 'blender']
            ]
        ],
        [
            'id' => 2,
            'title' => 'Creating Responsive Websites with Modern CSS Grid',
            'slug' => 'responsive-websites-with-css-grid',
            'excerpt' => 'Discover how to build flexible and responsive layouts using CSS Grid without relying on frameworks.',
            'content' => '<p>CSS Grid has revolutionized the way we create web layouts. Gone are the days of using complex float-based systems or relying entirely on frameworks like Bootstrap for grid layouts.</p>

<h2>What is CSS Grid?</h2>
<p>CSS Grid Layout is a two-dimensional grid system designed specifically for user interface design. It allows you to create complex and flexible layouts in both rows and columns simultaneously.</p>

<h2>Basic Grid Setup</h2>
<p>Here\'s a simple example of setting up a grid:</p>

<pre><code>.container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-gap: 20px;
}
</code></pre>

<p>This creates a three-column layout with equal widths and 20px gaps between grid items.</p>

<h2>Creating Responsive Layouts</h2>
<p>One of the most powerful features of CSS Grid is how easily it enables responsive design. Using media queries in combination with grid properties, you can completely redefine your layout at different screen sizes.</p>

<pre><code>@media (max-width: 768px) {
  .container {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .container {
    grid-template-columns: 1fr;
  }
}
</code></pre>

<h2>Advanced Grid Techniques</h2>
<p>Once you\'re comfortable with the basics, you can explore these more advanced techniques:</p>
<ul>
    <li><strong>Grid Areas</strong> - Name sections of your grid for easier placement</li>
    <li><strong>Auto-placement</strong> - Let the browser decide where items should go</li>
    <li><strong>Minmax function</strong> - Set flexible minimum and maximum sizes</li>
    <li><strong>Auto-fill and auto-fit</strong> - Create dynamic numbers of columns</li>
</ul>

<h2>Browser Support</h2>
<p>CSS Grid is supported in all modern browsers. For older browsers, you can use a feature detection and provide a fallback layout.</p>

<h2>Conclusion</h2>
<p>CSS Grid is a powerful tool that gives developers unprecedented control over layout design. By mastering grid, you can create complex, responsive layouts without the bloat of CSS frameworks.</p>',
            'featured_image' => 'blog2.jpg',
            'category_id' => 1,
            'category_name' => 'Articles',
            'category_slug' => 'articles',
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'is_featured' => 1,
            'views' => 187,
            'published_at' => '2025-04-10 14:15:00',
            'created_at' => '2025-04-09 20:30:00',
            'updated_at' => '2025-04-10 15:10:00',
            'tags' => [
                ['name' => 'CSS', 'slug' => 'css'],
                ['name' => 'Web Development', 'slug' => 'web-development'],
                ['name' => 'Responsive Design', 'slug' => 'responsive-design']
            ]
        ],
        [
            'id' => 3,
            'title' => 'Introduction to Arduino for Maker Projects',
            'slug' => 'introduction-to-arduino-for-makers',
            'excerpt' => 'Learn how to get started with Arduino for your DIY electronic projects.',
            'content' => '<p>Arduino has become the go-to platform for makers, hobbyists, artists, and students to create interactive electronic projects. Its combination of easy-to-use hardware and software makes it accessible even to those with no prior electronics experience.</p>

<h2>What is Arduino?</h2>
<p>Arduino is an open-source electronics platform based on easy-to-use hardware and software. It consists of a programmable circuit board (microcontroller) and software called the Arduino IDE (Integrated Development Environment) that you use to write and upload code to the physical board.</p>

<h2>Getting Started with Arduino</h2>
<p>To begin your Arduino journey, you\'ll need:</p>
<ul>
    <li>An Arduino board (Arduino Uno is recommended for beginners)</li>
    <li>USB A to B cable (to connect Arduino to your computer)</li>
    <li>Arduino IDE software (downloadable from arduino.cc)</li>
    <li>Basic electronic components (LEDs, resistors, breadboard, jumper wires)</li>
</ul>

<h2>Your First Arduino Project: Blinking LED</h2>
<p>The classic first Arduino project is making an LED blink. Here\'s the code:</p>

<pre><code>void setup() {
  // Initialize digital pin LED_BUILTIN as an output
  pinMode(LED_BUILTIN, OUTPUT);
}

void loop() {
  digitalWrite(LED_BUILTIN, HIGH);   // Turn the LED on
  delay(1000);                       // Wait for a second
  digitalWrite(LED_BUILTIN, LOW);    // Turn the LED off
  delay(1000);                       // Wait for a second
}
</code></pre>

<h2>Understanding Arduino Programming</h2>
<p>Arduino programming is based on C/C++, but you don\'t need to know these languages in depth to get started. The Arduino language simplifies many aspects of C++ to make it more accessible.</p>

<p>Every Arduino program (also called a "sketch") has two main functions:</p>
<ul>
    <li><strong>setup()</strong> - Runs once when the Arduino powers on or resets</li>
    <li><strong>loop()</strong> - Runs continuously after setup completes</li>
</ul>

<h2>Sensors and Input/Output</h2>
<p>Arduino can interact with a wide range of sensors and devices:</p>
<ul>
    <li>Temperature and humidity sensors</li>
    <li>Motion detectors</li>
    <li>Light sensors</li>
    <li>Buttons and switches</li>
    <li>Servos and motors</li>
    <li>LCD displays</li>
</ul>

<h2>Project Ideas for Beginners</h2>
<p>Once you\'ve mastered the basics, try these beginner-friendly projects:</p>
<ol>
    <li>Digital thermometer with temperature display</li>
    <li>Motion-activated light</li>
    <li>Simple robot that avoids obstacles</li>
    <li>Musical instrument with buttons and a speaker</li>
    <li>Plant watering reminder system</li>
</ol>

<h2>Resources for Learning More</h2>
<p>To deepen your Arduino knowledge, check out:</p>
<ul>
    <li>Arduino\'s official documentation and tutorials</li>
    <li>Adafruit and SparkFun learning resources</li>
    <li>Arduino Project Hub for inspiration</li>
    <li>Local makerspaces and community workshops</li>
</ul>

<p>Arduino opens up a world of possibilities for creating interactive projects. In future articles, I\'ll cover more advanced topics and share step-by-step guides for specific maker projects.</p>',
            'featured_image' => 'blog3.jpg',
            'category_id' => 2,
            'category_name' => 'Tutorials',
            'category_slug' => 'tutorials',
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'is_featured' => 0,
            'views' => 145,
            'published_at' => '2025-04-05 09:20:00',
            'created_at' => '2025-04-04 16:45:00',
            'updated_at' => '2025-04-05 10:30:00',
            'tags' => [
                ['name' => 'Arduino', 'slug' => 'arduino'],
                ['name' => 'Electronics', 'slug' => 'electronics'],
                ['name' => 'Maker', 'slug' => 'maker'],
                ['name' => 'DIY', 'slug' => 'diy']
            ]
        ],
        [
            'id' => 4,
            'title' => 'Essential Typography Tips for Graphic Designers',
            'slug' => 'typography-tips-for-designers',
            'excerpt' => 'Master the art of typography with these practical tips that will elevate your design work.',
            'content' => '<p>Typography is often called the art and technique of arranging type. Good typography enhances readability, establishes hierarchy, and helps communicate your message effectively.</p>

<h2>Why Typography Matters</h2>
<p>Typography accounts for about 95% of web design. It\'s not just about choosing pretty fonts – it\'s about creating an optimal experience for your readers and users. Good typography can make content easy to digest, establish brand identity, and evoke specific emotions.</p>

<h2>Font Selection Principles</h2>
<p>When selecting fonts for your projects, consider:</p>
<ul>
    <li><strong>Readability</strong> - Is the font easily readable at various sizes?</li>
    <li><strong>Personality</strong> - Does the font convey the right mood and tone?</li>
    <li><strong>Versatility</strong> - Does the font family offer enough weight variations?</li>
    <li><strong>Contrast</strong> - If using multiple fonts, do they complement each other?</li>
</ul>

<h2>Font Pairing Guidelines</h2>
<p>For effective font pairing:</p>
<ol>
    <li>Combine a serif with a sans-serif for good contrast</li>
    <li>Use fonts from the same family for subtle hierarchy</li>
    <li>Limit yourself to 2-3 font families per design</li>
    <li>Ensure the fonts share some common qualities (x-height, width, etc.)</li>
</ol>

<h2>Typography Hierarchy Techniques</h2>
<p>Create clear hierarchy in your designs using:</p>
<ul>
    <li><strong>Size contrast</strong> - Varying font sizes to indicate importance</li>
    <li><strong>Weight contrast</strong> - Using bold for emphasis</li>
    <li><strong>Color contrast</strong> - Using different colors to guide attention</li>
    <li><strong>Spacing</strong> - Using whitespace to group or separate elements</li>
    <li><strong>Style contrast</strong> - Mixing italic with regular styles</li>
</ul>

<h2>Practical Typography Tips</h2>
<p>Improve your typography with these practical tips:</p>
<ol>
    <li><strong>Line Length</strong> - Aim for 45-75 characters per line for optimal readability</li>
    <li><strong>Line Height</strong> - Set line height (leading) to about 1.5× the font size</li>
    <li><strong>Letter Spacing</strong> - Adjust tracking for headlines and all-caps text</li>
    <li><strong>Alignment</strong> - Use left alignment for longer text (avoid justified text on the web)</li>
    <li><strong>Contrast</strong> - Ensure sufficient contrast between text and background</li>
</ol>

<h2>Common Typography Mistakes to Avoid</h2>
<ul>
    <li>Using too many fonts in one design</li>
    <li>Poor contrast making text difficult to read</li>
    <li>Improper scaling of headings and body text</li>
    <li>Ignoring line height and letter spacing</li>
    <li>Not maintaining consistency throughout the design</li>
</ul>

<h2>Typography Resources</h2>
<p>To continue developing your typography skills, check out:</p>
<ul>
    <li>Typewolf for font inspiration and pairing ideas</li>
    <li>Google Fonts for free, web-friendly fonts</li>
    <li>"Thinking with Type" by Ellen Lupton (essential reading)</li>
    <li>Type scales calculators for harmonious sizing</li>
</ul>

<p>Remember, great typography often goes unnoticed because it creates a seamless reading experience. When done well, it quietly guides the reader through your content while reinforcing your message and brand identity.</p>',
            'featured_image' => 'blog4.jpg',
            'category_id' => 1,
            'category_name' => 'Articles',
            'category_slug' => 'articles',
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'is_featured' => 0,
            'views' => 122,
            'published_at' => '2025-03-28 11:15:00',
            'created_at' => '2025-03-27 14:30:00',
            'updated_at' => '2025-03-28 12:20:00',
            'tags' => [
                ['name' => 'Typography', 'slug' => 'typography'],
                ['name' => 'Graphic Design', 'slug' => 'graphic-design'],
                ['name' => 'Design Tips', 'slug' => 'design-tips']
            ]
        ],
        [
            'id' => 5,
            'title' => 'Building Your First Progressive Web App (PWA)',
            'slug' => 'building-your-first-pwa',
            'excerpt' => 'Learn how to create a Progressive Web App that works offline and feels like a native app.',
            'content' => '<p>Progressive Web Apps (PWAs) combine the best of web and mobile apps. They\'re fast, installable, reliable, and engaging. In this tutorial, we\'ll walk through creating your first PWA from scratch.</p>

<h2>What is a Progressive Web App?</h2>
<p>A Progressive Web App is a website built using modern web technologies that provides an app-like experience to users. Key features include:</p>
<ul>
    <li>Works offline or with poor network conditions</li>
    <li>Can be installed on the home screen</li>
    <li>Loads quickly, even on slow networks</li>
    <li>Feels like a native app with full-screen mode</li>
    <li>Supports push notifications</li>
</ul>

<h2>Core Components of a PWA</h2>
<p>To create a PWA, you\'ll need three main components:</p>
<ol>
    <li><strong>HTTPS</strong> - Your app must be served over a secure connection</li>
    <li><strong>Web App Manifest</strong> - A JSON file with metadata about your app</li>
    <li><strong>Service Worker</strong> - A JavaScript file that controls the caching and offline functionality</li>
</ol>

<h2>Setting Up Your Project</h2>
<p>First, create your project structure:</p>
<pre><code>my-first-pwa/
├── index.html
├── styles.css
├── app.js
├── manifest.json
└── sw.js (service worker)</code></pre>

<h2>Creating the Web App Manifest</h2>
<p>Your manifest.json file should look something like this:</p>

<pre><code>{
  "name": "My First PWA",
  "short_name": "FirstPWA",
  "start_url": "/index.html",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#f5b642",
  "icons": [
    {
      "src": "images/icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "images/icon-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}</code></pre>

<h2>Link the Manifest in Your HTML</h2>
<p>Add this line to the head of your index.html:</p>
<pre><code>&lt;link rel="manifest" href="manifest.json"&gt;</code></pre>

<h2>Creating a Basic Service Worker</h2>
<p>The service worker (sw.js) handles caching and offline functionality:</p>

<pre><code>const CACHE_NAME = \'my-pwa-cache-v1\';
const urlsToCache = [
  \'./\',
  \'./index.html\',
  \'./styles.css\',
  \'./app.js\'
];

// Install the service worker and cache assets
self.addEventListener(\'install\', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch from cache first, then network
self.addEventListener(\'fetch\', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});</code></pre>

<h2>Register the Service Worker</h2>
<p>Add this script to your app.js file:</p>

<pre><code>if (\'serviceWorker\' in navigator) {
  window.addEventListener(\'load\', () => {
    navigator.serviceWorker.register(\'./sw.js\')
      .then(registration => {
        console.log(\'ServiceWorker registration successful\');
      })
      .catch(err => {
        console.log(\'ServiceWorker registration failed: \', err);
      });
  });
}</code></pre>

<h2>Making the App Installable</h2>
<p>To allow users to install your PWA, you need to implement an "add to home screen" button:</p>

<pre><code>let deferredPrompt;

window.addEventListener(\'beforeinstallprompt\', (e) => {
  // Prevent Chrome from automatically showing the prompt
  e.preventDefault();
  // Stash the event so it can be triggered later
  deferredPrompt = e;
  // Show the install button
  document.getElementById(\'install-button\').style.display = \'block\';
});

document.getElementById(\'install-button\').addEventListener(\'click\', (e) => {
  // Hide the install button
  document.getElementById(\'install-button\').style.display = \'none\';
  // Show the install prompt
  deferredPrompt.prompt();
  // Wait for the user to respond to the prompt
  deferredPrompt.userChoice.then((choiceResult) => {
    if (choiceResult.outcome === \'accepted\') {
      console.log(\'User accepted the install prompt\');
    } else {
      console.log(\'User dismissed the install prompt\');
    }
    // Clear the deferredPrompt variable
    deferredPrompt = null;
  });
});</code></pre>

<h2>Testing Your PWA</h2>
<p>To test your PWA:</p>
<ol>
    <li>Serve your app over HTTPS (you can use GitHub Pages for testing)</li>
    <li>Open Chrome DevTools and go to the Application tab</li>
    <li>Check the Manifest and Service Worker sections for any issues</li>
    <li>Use the Lighthouse audit to evaluate your PWA implementation</li>
</ol>

<h2>Next Steps</h2>
<p>Once you\'ve created your basic PWA, consider adding:</p>
<ul>
    <li>Push notifications</li>
    <li>Background sync</li>
    <li>Advanced caching strategies</li>
    <li>Responsive design optimized for various devices</li>
</ul>

<p>Building PWAs is a powerful way to create cross-platform applications using web technologies. With the basics covered in this tutorial, you\'re well on your way to creating fast, reliable, and engaging web applications that work for all your users.</p>',
            'featured_image' => 'blog5.jpg',
            'category_id' => 2,
            'category_name' => 'Tutorials',
            'category_slug' => 'tutorials',
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'is_featured' => 0,
            'views' => 98,
            'published_at' => '2025-03-20 13:45:00',
            'created_at' => '2025-03-19 11:20:00',
            'updated_at' => '2025-03-20 14:30:00',
            'tags' => [
                ['name' => 'PWA', 'slug' => 'pwa'],
                ['name' => 'JavaScript', 'slug' => 'javascript'],
                ['name' => 'Web Development', 'slug' => 'web-development'],
                ['name' => 'App Development', 'slug' => 'app-development']
            ]
        ]
    ],
    'resources' => [
        [
            'id' => 1,
            'title' => 'Responsive CSS Grid Cheat Sheet',
            'slug' => 'responsive-css-grid-cheat-sheet',
            'description' => 'A comprehensive reference guide for CSS Grid properties and techniques, with examples for responsive layouts.',
            'file_path' => 'resources/responsive-css-grid-cheat-sheet.pdf',
            'file_size' => 1258291, // approximately 1.2 MB
            'file_type' => 'application/pdf',
            'thumbnail' => 'resources/thumbnails/css-grid-thumb.jpg',
            'download_count' => 143,
            'requires_login' => false,
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'created_at' => '2025-03-15 10:30:00',
            'updated_at' => '2025-03-15 10:30:00'
        ],
        [
            'id' => 2,
            'title' => 'Blender Keyboard Shortcuts Poster',
            'slug' => 'blender-keyboard-shortcuts',
            'description' => 'Printable poster with all essential Blender keyboard shortcuts, organized by function.',
            'file_path' => 'resources/blender-shortcuts-poster.pdf',
            'file_size' => 2153984, // approximately 2.1 MB
            'file_type' => 'application/pdf',
            'thumbnail' => 'resources/thumbnails/blender-shortcuts-thumb.jpg',
            'download_count' => 98,
            'requires_login' => false,
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'created_at' => '2025-03-10 15:45:00',
            'updated_at' => '2025-03-10 15:45:00'
        ],
        [
            'id' => 3,
            'title' => 'Arduino Starter Kit Project Files',
            'slug' => 'arduino-starter-kit-files',
            'description' => 'Complete code samples and circuit diagrams for 10 beginner Arduino projects.',
            'file_path' => 'resources/arduino-starter-kit.zip',
            'file_size' => 4521984, // approximately 4.3 MB
            'file_type' => 'application/zip',
            'thumbnail' => 'resources/thumbnails/arduino-kit-thumb.jpg',
            'download_count' => 76,
            'requires_login' => true,
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'created_at' => '2025-03-05 09:20:00',
            'updated_at' => '2025-03-05 09:20:00'
        ],
        [
            'id' => 4,
            'title' => 'Typography Fundamentals eBook',
            'slug' => 'typography-fundamentals-ebook',
            'description' => 'A 50-page eBook covering typography basics, principles, and practical applications for designers.',
            'file_path' => 'resources/typography-fundamentals.pdf',
            'file_size' => 8126464, // approximately 7.8 MB
            'file_type' => 'application/pdf',
            'thumbnail' => 'resources/thumbnails/typography-ebook-thumb.jpg',
            'download_count' => 112,
            'requires_login' => true,
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'created_at' => '2025-02-25 13:10:00',
            'updated_at' => '2025-02-25 13:10:00'
        ],
        [
            'id' => 5,
            'title' => 'Web Design Color Palette Templates',
            'slug' => 'web-design-color-palettes',
            'description' => 'Collection of 15 carefully crafted color palettes in Adobe Swatch Exchange format, suitable for various types of websites.',
            'file_path' => 'resources/web-color-palettes.zip',
            'file_size' => 1572864, // approximately 1.5 MB
            'file_type' => 'application/zip',
            'thumbnail' => 'resources/thumbnails/color-palettes-thumb.jpg',
            'download_count' => 189,
            'requires_login' => false,
            'author_id' => 1,
            'author_name' => 'vuyani',
            'status' => 'published',
            'created_at' => '2025-02-18 16:40:00',
            'updated_at' => '2025-02-18 16:40:00'
        ]
    ],
'featuredPosts' => [
        [
            'id' => 1,
            'title' => 'Getting Started with 3D Modeling for Beginners',
            'slug' => 'getting-started-with-3d-modeling',
            'excerpt' => 'Learn the basics of 3D modeling with this comprehensive guide for absolute beginners.',
            'featured_image' => 'blog1.jpg',
            'category_id' => 1,
            'category_name' => 'Articles',
            'category_slug' => 'articles',
            'published_at' => '2025-04-15 10:30:00'
        ],
        [
            'id' => 2,
            'title' => 'Creating Responsive Websites with Modern CSS Grid',
            'slug' => 'responsive-websites-with-css-grid',
            'excerpt' => 'Discover how to build flexible and responsive layouts using CSS Grid without relying on frameworks.',
            'featured_image' => 'blog2.jpg',
            'category_id' => 1,
            'category_name' => 'Articles',
            'category_slug' => 'articles',
            'published_at' => '2025-04-10 14:15:00'
        ],
        [
            'id' => 3,
            'title' => 'Introduction to Arduino for Maker Projects',
            'slug' => 'introduction-to-arduino-for-makers',
            'excerpt' => 'Learn how to get started with Arduino for your DIY electronic projects.',
            'featured_image' => 'blog3.jpg',
            'category_id' => 2,
            'category_name' => 'Tutorials',
            'category_slug' => 'tutorials',
            'published_at' => '2025-04-05 09:20:00'
        ]
    ],
    'categories' => [
        [
            'id' => 1,
            'name' => 'Articles',
            'slug' => 'articles',
            'description' => 'General articles about design and development'
        ],
        [
            'id' => 2,
            'name' => 'Tutorials',
            'slug' => 'tutorials',
            'description' => 'Step-by-step tutorials and guides'
        ],
        [
            'id' => 3,
            'name' => 'Resources',
            'slug' => 'resources',
            'description' => 'Downloadable resources and tools'
        ]
    ]
];