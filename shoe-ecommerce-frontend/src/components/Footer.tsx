import { Link } from 'react-router-dom';

const Footer = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-blue-900 text-white">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* Company Info */}
          <div>
            <div className="flex items-center mb-4">
              <div className="h-8 w-8 bg-white text-blue-900 rounded-full flex items-center justify-center font-bold">
                S
              </div>
              <span className="ml-2 text-xl font-bold">ShoeShop</span>
            </div>
            <p className="text-blue-200 mb-6">
              Premium footwear for every occasion. Quality, comfort, and style combined.
            </p>
            <div className="flex space-x-4">
              <a 
                href="https://facebook.com" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="text-white hover:text-blue-200 transition-colors"
                aria-label="Facebook"
              >
                <span className="w3-large">&#xf09a;</span>
              </a>
              <a 
                href="https://instagram.com" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="text-white hover:text-blue-200 transition-colors"
                aria-label="Instagram"
              >
                <span className="w3-large">&#xf16d;</span>
              </a>
              <a 
                href="https://twitter.com" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="text-white hover:text-blue-200 transition-colors"
                aria-label="Twitter"
              >
                <span className="w3-large">&#xf099;</span>
              </a>
              <a 
                href="https://youtube.com" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="text-white hover:text-blue-200 transition-colors"
                aria-label="YouTube"
              >
                <span className="w3-large">&#xf167;</span>
              </a>
            </div>
          </div>
          
          {/* Quick Links */}
          <div>
            <h3 className="text-lg font-bold mb-4">Quick Links</h3>
            <ul className="space-y-2">
              <li>
                <Link to="/" className="text-blue-200 hover:text-white transition-colors">
                  <span className="text-xs mr-2">&#9654;</span>
                  Home
                </Link>
              </li>
              <li>
                <Link to="/products" className="text-blue-200 hover:text-white transition-colors">
                  <span className="text-xs mr-2">&#9654;</span>
                  Shop
                </Link>
              </li>
              <li>
                <Link to="/sale" className="text-blue-200 hover:text-white transition-colors">
                  <span className="text-xs mr-2">&#9654;</span>
                  Sale
                </Link>
              </li>
              <li>
                <Link to="/about" className="text-blue-200 hover:text-white transition-colors">
                  <span className="text-xs mr-2">&#9654;</span>
                  About Us
                </Link>
              </li>
              <li>
                <Link to="/contact" className="text-blue-200 hover:text-white transition-colors">
                  <span className="text-xs mr-2">&#9654;</span>
                  Contact
                </Link>
              </li>
            </ul>
          </div>
          
          {/* Customer Service */}
          <div>
            <h3 className="text-lg font-bold mb-4">Customer Service</h3>
            <ul className="space-y-2">
              <li>
                <Link to="/faq" className="text-blue-200 hover:text-white transition-colors">
                  <i className="fas fa-chevron-right mr-2 text-xs"></i>
                  FAQ
                </Link>
              </li>
              <li>
                <Link to="/shipping" className="text-blue-200 hover:text-white transition-colors">
                  <i className="fas fa-chevron-right mr-2 text-xs"></i>
                  Shipping & Returns
                </Link>
              </li>
              <li>
                <Link to="/warranty" className="text-blue-200 hover:text-white transition-colors">
                  <i className="fas fa-chevron-right mr-2 text-xs"></i>
                  Warranty
                </Link>
              </li>
              <li>
                <Link to="/payment" className="text-blue-200 hover:text-white transition-colors">
                  <i className="fas fa-chevron-right mr-2 text-xs"></i>
                  Payment Methods
                </Link>
              </li>
              <li>
                <Link to="/track-order" className="text-blue-200 hover:text-white transition-colors">
                  <i className="fas fa-chevron-right mr-2 text-xs"></i>
                  Track Order
                </Link>
              </li>
            </ul>
          </div>
          
          {/* Contact Info */}
          <div>
            <h3 className="text-lg font-bold mb-4">Contact Us</h3>
            <ul className="space-y-3">
              <li className="flex items-start">
                <span className="text-blue-200 mr-3 mt-1">&#9906;</span>
                <span>123 Shoe Street, Fashion District, New York, NY 10001</span>
              </li>
              <li className="flex items-center">
                <span className="text-blue-200 mr-3">&#9742;</span>
                <span>+1 (234) 567-890</span>
              </li>
              <li className="flex items-center">
                <span className="text-blue-200 mr-3">&#9993;</span>
                <span>support@shoeshop.com</span>
              </li>
              <li className="flex items-center">
                <span className="text-blue-200 mr-3">&#128338;</span>
                <span>Mon-Fri: 9AM - 6PM</span>
              </li>
            </ul>
          </div>

          {/* Payment Methods */}
          <div>
            <h3 className="text-lg font-bold mb-4">Payment Methods</h3>
            <div className="grid grid-cols-3 gap-2">
              <div className="bg-white text-blue-900 rounded p-2 flex items-center justify-center">
                <span className="font-bold text-sm">Visa</span>
              </div>
              <div className="bg-white text-blue-900 rounded p-2 flex items-center justify-center">
                <span className="font-bold text-sm">MasterCard</span>
              </div>
              <div className="bg-white text-blue-900 rounded p-2 flex items-center justify-center">
                <span className="font-bold text-sm">PayPal</span>
              </div>
              <div className="bg-white text-blue-900 rounded p-2 flex items-center justify-center">
                <span className="font-bold text-sm">ApplePay</span>
              </div>
              <div className="bg-white text-blue-900 rounded p-2 flex items-center justify-center">
                <span className="font-bold text-sm">GooglePay</span>
              </div>
              <div className="bg-white text-blue-900 rounded p-2 flex items-center justify-center">
                <span className="font-bold text-sm">Alipay</span>
              </div>
            </div>
          </div>
        </div>
        
        <hr className="border-blue-800 my-8" />
        
        <div className="flex flex-col md:flex-row justify-between items-center">
          <p className="text-blue-200 text-sm mb-4 md:mb-0">
            &copy; {currentYear} ShoeShop. All rights reserved.
          </p>
          <div className="flex space-x-4 text-sm">
            <Link to="/privacy" className="text-blue-200 hover:text-white transition-colors">
              Privacy Policy
            </Link>
            <Link to="/terms" className="text-blue-200 hover:text-white transition-colors">
              Terms of Service
            </Link>
            <Link to="/shipping" className="text-blue-200 hover:text-white transition-colors">
              Shipping Info
            </Link>
            <Link to="/returns" className="text-blue-200 hover:text-white transition-colors">
              Returns & Refunds
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer; 