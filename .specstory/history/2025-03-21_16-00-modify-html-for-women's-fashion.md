## SpecStory

## Modify HTML for Women's Fashion (3/21/2025, 4:00:06 PM)

_**User**_

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urban Runner Sneakers - Trendy Footwear</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        
        h1, h2, h3, h4, h5 {
            color: #333;
            margin-top: 1.5em;
            margin-bottom: 0.8em;
        }
        
        h1 {
            font-size: 2em;
            margin-top: 0;
            margin-bottom: 0.5em;
        }
        
        h2 {
            font-size: 1.5em;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.3em;
        }

        p {
            margin-bottom: 1em;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .breadcrumbs {
            margin-bottom: 20px;
            color: #666;
            font-size: 0.9em;
        }
        
        .breadcrumbs a {
            color: #4CAF50;
            text-decoration: none;
        }
        
        .breadcrumbs a:hover {
            text-decoration: underline;
        }
        
        .product-details {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .product-gallery {
            flex: 1;
            min-width: 300px;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            background-color: #f5f5f5;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
        }
        
        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .main-image:hover img {
            transform: scale(1.05);
        }
        
        .thumbnails {
            display: flex;
            gap: 10px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            background-color: #f5f5f5;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .thumbnail:hover {
            border-color: #4CAF50;
            transform: translateY(-2px);
        }
        
        .thumbnail.active {
            border-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            flex: 1;
            min-width: 300px;
        }
        
        .product-title {
            margin-top: 0;
            font-size: 1.8em;
            margin-bottom: 10px;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stars {
            color: #ffc107;
            font-size: 1.2em;
            margin-right: 10px;
        }
        
        .review-count {
            color: #4CAF50;
            font-size: 0.9em;
            cursor: pointer;
        }
        
        .review-count:hover {
            text-decoration: underline;
        }
        
        .product-price {
            font-size: 1.8em;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.7em;
            margin-left: 10px;
        }
        
        .product-description {
            margin-bottom: 20px;
            line-height: 1.7;
        }
        
        .product-features {
            margin-bottom: 25px;
        }
        
        .product-features ul {
            list-style-type: none;
            padding-left: 0;
        }
        
        .product-features li {
            padding-left: 25px;
            position: relative;
            margin-bottom: 10px;
        }
        
        .product-features li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: bold;
        }
        
        .product-options {
            margin-bottom: 25px;
        }
        
        .option-label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }
        
        .size-options, .color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .size-option {
            display: inline-block;
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .size-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .size-option.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
            transform: translateY(-2px);
        }
        
        .color-option {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            border: 2px solid transparent;
        }
        
        .color-option:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .color-option.active {
            box-shadow: 0 0 0 2px white, 0 0 0 4px #4CAF50;
            transform: scale(1.1);
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            background-color: #f1f3f4;
            border: none;
            border-radius: 4px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background-color: #4CAF50;
            color: white;
            transform: translateY(-2px);
        }
        
        .quantity-input {
            width: 50px;
            height: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 10px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .quantity-input:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 2px rgba(76,175,80,0.2);
        }
        
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn:active::after {
            width: 300px;
            height: 300px;
        }
        
        .btn-secondary {
            background-color: #f1f3f4;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #ddd;
        }
        
        .btn-full {
            width: 100%;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .wishlist-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 20px;
            background: #f1f3f4;
        }
        
        .wishlist-btn:hover {
            color: #e74c3c;
            background: #fde8e8;
            transform: translateY(-2px);
        }
        
        .wishlist-btn.active {
            color: #e74c3c;
            background: #fde8e8;
        }
        
        .wishlist-btn.active::before {
            content: '♥';
            animation: heartBeat 0.3s ease;
        }
        
        @keyframes heartBeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .product-meta {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 25px;
        }
        
        .product-meta div {
            margin-bottom: 5px;
        }
        
        .product-meta span {
            font-weight: bold;
        }
        
        .product-share {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .share-icon {
            width: 35px;
            height: 35px;
            background-color: #f1f3f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
            cursor: pointer;
        }
        
        .share-icon:hover {
            background-color: #4CAF50;
            color: white;
        }
        
        .product-tabs {
            margin-top: 50px;
        }
        
        .tabs-header {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 15px 25px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-button:hover {
            color: #4CAF50;
        }
        
        .tab-button.active {
            color: #4CAF50;
            border-bottom-color: #4CAF50;
        }
        
        .tab-content {
            padding: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            display: none;
        }
        
        .tab-content.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        .specifications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .specifications-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .specifications-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .specifications-table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        
        .review {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }
        
        .review:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .reviewer-name {
            font-weight: bold;
        }
        
        .review-date {
            color: #666;
            font-size: 0.9em;
        }
        
        .related-products {
            margin-top: 50px;
        }
        
        .related-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .related-product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }
        
        .related-product-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .related-product-card:hover {
            transform: translateY(-5px);
        }
        
        .related-product-image {
            width: 100%;
            height: 180px;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .related-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .related-product-content {
            padding: 15px;
        }
        
        .related-product-content h3 {
            margin-top: 0;
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        
        .related-product-content .price {
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
       
        <div class="product-details">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="https://via.placeholder.com/80x80?text=Image+1" alt="Urban Runner Sneakers - View 1" alt="Urban Runner Sneakers">
                </div>
                <div class="thumbnails">
                    <div class="thumbnail active">
                        <img src="https://via.placeholder.com/80x80?text=Image+1" alt="Urban Runner Sneakers - View 1">
                    </div>
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/80x80?text=Image+2" alt="Urban Runner Sneakers - View 2">
                    </div>
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/80x80?text=Image+3" alt="Urban Runner Sneakers - View 3">
                    </div>
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/80x80?text=Image+4" alt="Urban Runner Sneakers - View 4">
                    </div>
                </div>
            </div>
            
            <div class="product-info">
                <h1 class="product-title">Urban Runner Sneakers</h1>
                
                <div class="product-rating">
                    <div class="stars">★★★★☆</div>
                    <div class="review-count">36 Reviews</div>
                </div>
                
                <div class="product-price">
                    $89.99 <span class="original-price">$129.99</span>
                </div>
                
                <div class="product-description">
                    <p>The Urban Runner sneakers are designed for the modern city explorer. With lightweight mesh construction and responsive cushioning, these sneakers provide all-day comfort while navigating urban landscapes. The sleek design makes them versatile enough for casual outings or light athletic activities.</p>
                </div>
                
                <div class="product-features">
                    <ul>
                        <li>Breathable mesh upper for enhanced ventilation</li>
                        <li>EVA midsole with responsive cushioning</li>
                        <li>Durable rubber outsole with multidirectional traction</li>
                        <li>Padded collar and tongue for added comfort</li>
                        <li>Reflective details for visibility in low light</li>
                    </ul>
                </div>
                
                <div class="product-options">
                    <div class="option-label">Size</div>
                    <div class="size-options">
                        <div class="size-option">7</div>
                        <div class="size-option">7.5</div>
                        <div class="size-option active">8</div>
                        <div class="size-option">8.5</div>
                        <div class="size-option">9</div>
                        <div class="size-option">9.5</div>
                        <div class="size-option">10</div>
                        <div class="size-option">10.5</div>
                        <div class="size-option">11</div>
                    </div>
                    
                    <div class="option-label">Color</div>
                    <div class="color-options">
                        <div class="color-option active" style="background-color: #3498db;"></div>
                        <div class="color-option" style="background-color: #2ecc71;"></div>
                        <div class="color-option" style="background-color: #e74c3c;"></div>
                        <div class="color-option" style="background-color: #f1c40f;"></div>
                        <div class="color-option" style="background-color: #34495e;"></div>
                    </div>
                </div>
                
                <div class="quantity-selector">
                    <div class="option-label">Quantity</div>
                    <button class="quantity-btn">-</button>
                    <input type="text" class="quantity-input" value="1">
                    <button class="quantity-btn">+</button>
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-full" onclick="showToast('Added to Cart!')">Add to Cart</button>
                    <button class="btn btn-secondary" onclick="showToast('Proceeding to Checkout!')">Buy Now</button>
                </div>
                
                <div class="wishlist-btn" onclick="toggleWishlist(this)">
                    ♡ Add to Wishlist
                </div>
                
                <div class="product-meta">
                    <div><span>SKU:</span> URN-BLU-22789</div>
                    <div><span>Category:</span> Sneakers</div>
                    <div><span>Tags:</span> Running, Casual, Lightweight</div>
                </div>
                
                <div class="product-share">
                    <div class="share-icon">f</div>
                    <div class="share-icon">t</div>
                    <div class="share-icon">in</div>
                    <div class="share-icon">P</div>
                </div>
            </div>
        </div>
        
        <div class="product-tabs">
            <div class="tabs-header">
                <div class="tab-button active">Description</div>
                <div class="tab-button">Specifications</div>
                <div class="tab-button">Reviews (36)</div>
            </div>
            
            <div class="tab-content active">
                <h2>Product Description</h2>
                <p>Urban Runner sneakers combine style, comfort, and functionality for the modern urban explorer. Engineered with a breathable mesh upper and responsive cushioning, these sneakers are perfect for all-day wear in city environments.</p>
                
                <p>The lightweight construction reduces fatigue during extended wear, while the durable rubber outsole provides reliable traction on various surfaces. Strategic padding around the collar and tongue enhances comfort, and reflective details improve visibility in low-light conditions.</p>
                
                <p>Whether you're commuting, exploring the city, or engaging in light athletic activities, the Urban Runner sneakers deliver the perfect balance of style and performance. The versatile design pairs easily with casual or athletic attire, making these sneakers an essential addition to your footwear collection.</p>
                
                <p>Available in multiple colorways and sizes, Urban Runner sneakers are designed to suit your personal style while providing the comfort and support you need for your urban adventures.</p>
            </div>
            
            <div class="tab-content">
                <h2>Product Specifications</h2>
                <table class="specifications-table">
                    <tr>
                        <td>Upper Material</td>
                        <td>Premium leather with mesh panels</td>
                    </tr>
                    <tr>
                        <td>Lining</td>
                        <td>Breathable mesh with moisture-wicking technology</td>
                    </tr>
                    <tr>
                        <td>Outsole</td>
                        <td>Durable rubber with anti-slip pattern</td>
                    </tr>
                    <tr>
                        <td>Closure</td>
                        <td>Traditional lace-up with padded tongue</td>
                    </tr>
                    <tr>
                        <td>Weight</td>
                        <td>8.5 oz (240g) per shoe (size 9)</td>
                    </tr>
                    <tr>
                        <td>Drop</td>
                        <td>10mm heel-to-toe offset</td>
                    </tr>
                    <tr>
                        <td>Features</td>
                        <td>Breathable mesh, Anti-slip sole, Padded collar</td>
                    </tr>
                    <tr>
                        <td>Care Instructions</td>
                        <td>Wipe clean with damp cloth, air dry naturally</td>
                    </tr>
                </table>
            </div>
            
            <div class="tab-content">
                <h2>Customer Reviews</h2>
                
                <div class="review">
                    <div class="review-header">
                        <div class="reviewer-name">John D.</div>
                        <div class="review-date">October 1, 2023</div>
                    </div>
                    <div class="stars">★★★★★</div>
                    <p>Perfect fit and incredibly comfortable! The cushioning is just right, and the breathable mesh keeps my feet cool even during long walks. The design is modern and versatile - I can wear these with both casual and semi-formal outfits.</p>
                </div>
                
                <div class="review">
                    <div class="review-header">
                        <div class="reviewer-name">Emma S.</div>
                        <div class="review-date">September 28, 2023</div>
                    </div>
                    <div class="stars">★★★★★</div>
                    <p>These sneakers exceeded my expectations! The quality is outstanding, and they're so lightweight. I've been wearing them daily for a month now, and they still look brand new. The anti-slip sole is a great feature for rainy days.</p>
                </div>
                
                <div class="review">
                    <div class="review-header">
                        <div class="reviewer-name">Mike R.</div>
                        <div class="review-date">September 25, 2023</div>
                    </div>
                    <div class="stars">★★★★☆</div>
                    <p>Great sneakers overall! The comfort level is excellent, and they're very stylish. The only minor issue is that they run slightly narrow, so I'd recommend going up half a size if you have wider feet. Otherwise, perfect!</p>
                </div>
                
                <div class="review">
                    <div class="review-header">
                        <div class="reviewer-name">Sarah L.</div>
                        <div class="review-date">September 20, 2023</div>
                    </div>
                    <div class="stars">★★★★★</div>
                    <p>I'm in love with these sneakers! The design is modern and clean, and they're incredibly comfortable for all-day wear. The breathable mesh is perfect for summer, and the padding around the ankle provides excellent support.</p>
                </div>
                
                <div class="review">
                    <div class="review-header">
                        <div class="reviewer-name">David K.</div>
                        <div class="review-date">September 15, 2023</div>
                    </div>
                    <div class="stars">★★★★★</div>
                    <p>Best sneakers I've bought in years! The quality is exceptional, and they're so comfortable that I forget I'm wearing them. The anti-slip sole gives me confidence on wet surfaces, and the design is versatile enough for any occasion.</p>
                </div>
            </div>
            
            
        </div>
        
        <div class="related-products">
            <div class="related-header">
                <h2>You May Also Like</h2>
            </div>
            
            <div class="related-product-grid">
                <div class="related-product-card">
                    <div class="related-product-image">
                        <img src="https://via.placeholder.com/220x180?text=Street+Style" alt="Street Style Sneakers">
                    </div>
                    <div class="related-product-content">
                        <h3>Street Style</h3>
                        <div class="price">$75.00</div>
                        <a href="product_details.html" class="btn" style="width: 100%;">View Details</a>
                    </div>
                </div>
                
                <div class="related-product-card">
                    <div class="related-product-image">
                        <img src="https://via.placeholder.com/220x180?text=Performance+Pro" alt="Performance Pro Sneakers">
                    </div>
                    <div class="related-product-content">
                        <h3>Performance Pro</h3>
                        <div class="price">$129.99</div>
                        <a href="product_details.html" class="btn" style="width: 100%;">View Details</a>
                    </div>
                </div>
                
                <div class="related-product-card">
                    <div class="related-product-image">
                        <img src="https://via.placeholder.com/220x180?text=Retro+Vibe" alt="Retro Vibe Sneakers">
                    </div>
                    <div class="related-product-content">
                        <h3>Retro Vibe</h3>
                        <div class="price">$95.00</div>
                        <a href="product_details.html" class="btn" style="width: 100%;">View Details</a>
                    </div>
                </div>
                
                <div class="related-product-card">
                    <div class="related-product-image">
                        <img src="https://via.placeholder.com/220x180?text=Air+Glide" alt="Air Glide Sneakers">
                    </div>
                    <div class="related-product-content">
                        <h3>Air Glide</h3>
                        <div class="price">$110.00</div>
                        <a href="product_details.html" class="btn" style="width: 100%;">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <script>
        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #4CAF50;
                color: white;
                padding: 12px 24px;
                border-radius: 4px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                z-index: 1000;
                animation: slideUp 0.3s ease, fadeOut 0.3s ease 2s forwards;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2300);
        }

        function toggleWishlist(btn) {
            btn.classList.toggle('active');
            const isActive = btn.classList.contains('active');
            btn.innerHTML = isActive ? '♥ Added to Wishlist' : '♡ Add to Wishlist';
            showToast(isActive ? 'Added to Wishlist!' : 'Removed from Wishlist!');
        }

        function switchTab(index) {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            tabButtons[index].classList.add('active');
            tabContents[index].classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach((button, index) => {
                button.addEventListener('click', () => switchTab(index));
            });
        });

        const slideUp = {
            from: { transform: 'translate(-50%, 100%)', opacity: 0 },
            to: { transform: 'translate(-50%, 0)', opacity: 1 }
        };

        const fadeOut = {
            from: { opacity: 1 },
            to: { opacity: 0 }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.querySelector('.main-image img');
            const thumbnails = document.querySelectorAll('.thumbnail');
            
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', () => {
                    const newSrc = thumb.querySelector('img').src;
                    mainImage.style.opacity = '0';
                    
                    setTimeout(() => {
                        mainImage.src = newSrc;
                        mainImage.style.opacity = '1';
                    }, 300);
                    
                    thumbnails.forEach(t => t.classList.remove('active'));
                    thumb.classList.add('active');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const colorOptions = document.querySelectorAll('.color-option');
            
            colorOptions.forEach(option => {
                option.addEventListener('click', () => {
                    colorOptions.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sizeOptions = document.querySelectorAll('.size-option');
            
            sizeOptions.forEach(option => {
                option.addEventListener('click', () => {
                    sizeOptions.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.querySelector('.quantity-input');
            const minusBtn = document.querySelector('.quantity-btn:first-child');
            const plusBtn = document.querySelector('.quantity-btn:last-child');
            
            minusBtn.addEventListener('click', () => {
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    quantityInput.value = value - 1;
                }
            });
            
            plusBtn.addEventListener('click', () => {
                let value = parseInt(quantityInput.value);
                quantityInput.value = value + 1;
            });
            
            quantityInput.addEventListener('change', () => {
                let value = parseInt(quantityInput.value);
                if (isNaN(value) || value < 1) {
                    quantityInput.value = 1;
                }
            });
        });
    </script>
</body>
</html> 帮我改成适合女装的代码

---

_**Assistant**_

我将帮您将代码改成适合女装的产品展示页面。

---

_****_

Cancelled

---

_**User**_

我要改成xs,s,m,l size

---

_**Assistant**_

我将帮您将产品页面改成女装，并修改尺码为XS、S、M、L。以下是改好的代码：

---

_****_

Cancelled

---

_****_



---



---

