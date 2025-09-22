import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
// Reads from environment variable, falls back to default dev URL
// Ensure you have VITE_API_BASE_URL (or REACT_APP_BACKEND_ASSET_URL for CRA) set in your .env file
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Local Image URLs (Cream/Ivory Colorway - Now with Absolute Paths) ---
const heroImageUrl = `${backendAssetUrl}/images/9f898214_4d44_462c_820c_c02939ac32c8.png`; 
const featureImage1 = `${backendAssetUrl}/images/75a5c43e_d10a_4185_ba42_5669fbc8023c.png`; // Side view
const featureImage2 = `${backendAssetUrl}/images/641069c2_8d4d_4a92_b106_947869622aff.jpg`; // Upper view - Corrected filename
const featureImage3 = `${backendAssetUrl}/images/d3dff828_00ac_472e_a238_ffc5025057a1.jpg`; // Sole view
// --- End Image URLs ---

// Animation Variants
const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.2, // Stagger children animations
    },
  },
};

const itemVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { duration: 0.6, ease: "easeOut" }
  },
};

const imageVariants = {
  hidden: { opacity: 0, scale: 0.9 },
  visible: {
    opacity: 1,
    scale: 1,
    transition: { duration: 0.8, ease: "easeOut" } // Changed ease from custom cubic-bezier
  },
};

const NikePegasusPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"], // Track scroll from top of hero to bottom of hero starts leaving viewport
  });

  // Parallax effect: Move background slower than scroll
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "30%"]);
  // Fade out content faster
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.5, 1], [1, 1, 0]); 
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "50%"]);

  return (
    <div className="bg-gradient-to-b from-gray-50 to-white overflow-x-hidden"> {/* Prevent horizontal overflow */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden"> {/* Increased height */}
        {/* Parallax Background */}
        <motion.div
          className="absolute inset-0 z-0 bg-white" // Added bg-white as fallback
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'contain', // Changed to contain for product shot
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center center',
            y: backgroundY, // Apply parallax transformation
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent z-10"></div> {/* Softer Overlay */}
        
        {/* Content */}
        <motion.div 
          className="relative z-20 flex flex-col items-center justify-end pb-16 md:pb-24 h-full text-white text-center p-4" // Position content lower
          style={{ y: contentY, opacity: contentOpacity }} // Apply scroll-based transformations
        >
          <motion.h1 
            className="text-4xl md:text-6xl lg:text-7xl font-extrabold mb-4 tracking-tighter text-shadow-md" // Added text shadow
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            Nike Air Zoom Pegasus 41
          </motion.h1>
          <motion.p 
            className="text-xl md:text-2xl font-light mb-8 max-w-2xl mx-auto text-shadow-sm" // Added text shadow
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            Your Responsive Workhorse with Wings. Feel the Energy.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link 
              to="/products" // Link back to general products or specific category
              className="bg-white text-blue-600 font-semibold py-3 px-8 rounded-full hover:bg-gray-200 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Shop Now
            </Link>
          </motion.div>
        </motion.div>
      </div>

      {/* --- Intro Section --- */}
      <motion.div 
        className="container mx-auto px-6 py-16 md:py-24 text-center"
        variants={containerVariants}
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.3 }} // Trigger animation when 30% visible
      >
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Familiar Feel, Enhanced Energy</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Experience the familiar, just-for-you feel of the Pegasus, now updated for an even smoother, more responsive ride. The Pegasus 41 continues its legacy as a trusted daily trainer, infused with ReactX foam for incredible energy return.
        </motion.p>
      </motion.div>

      {/* --- Features Section (Alternating Layout with Stagger) --- */}
      <motion.div 
        className="container mx-auto px-6 py-16 md:py-24 space-y-20 md:space-y-28" // Increased spacing
        variants={containerVariants} // Use container variant for stagger
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }} // Trigger when 20% visible
      >
        {/* Feature 1: ReactX Foam */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}> {/* Use item variant for stagger delay */}
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Boost Your Run with ReactX</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              The all-new ReactX foam midsole provides over 13% more energy return compared to React foam, helping you stay fresh longer. It's springy, supportive, and built for the long haul.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Responsive cushioning</li>
              <li>Increased energy return</li>
              <li>Durable for daily training</li>
            </ul>
          </div>
          <motion.div 
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-100" // Added bg color 
            variants={imageVariants} // Specific animation for image
            whileHover={{ scale: 1.03 }} // Subtle hover scale
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage1} alt="Pegasus 41 Side View - ReactX Foam" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: Engineered Mesh Upper */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div 
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-gray-100"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage2} alt="Pegasus 41 Upper View - Engineered Mesh" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Breathable Comfort</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              The engineered mesh upper is now lighter, more breathable, and more comfortable than previous Pegasus models. It adapts to your foot for a personalized fit, mile after mile.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Enhanced breathability</li>
              <li>Lightweight construction</li>
              <li>Adaptive fit</li>
            </ul>
          </div>
        </motion.div>
        
        {/* Feature 3: Zoom Air Units / Sole */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Durable Traction</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
             The signature waffle-inspired rubber outsole provides reliable traction and flexibility on various road surfaces. Zoom Air units in the forefoot and heel contribute to the energized ride.
            </p>
             <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Waffle-inspired outsole</li>
               <li>Forefoot & Heel Zoom Air</li>
              <li>Reliable traction</li>
            </ul>
          </div>
          <motion.div 
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-100"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage3} alt="Pegasus 41 Sole View" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div 
        className="bg-gray-900 text-white" // Darker background
        initial={{ opacity: 0 }}
        whileInView={{ opacity: 1 }}
        viewport={{ once: true, amount: 0.5 }}
        transition={{ duration: 0.8 }}
      >
        <div className="container mx-auto px-6 py-20 md:py-28 text-center">
          <motion.h2 
            className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
          >
            Ready to Fly?
          </motion.h2>
          <motion.p 
            className="text-lg text-gray-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Experience the next evolution of the Nike Pegasus. Find your perfect pair and elevate your run.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link 
              to="/products" // Link back to general products or specific category
              className="bg-blue-600 text-white font-semibold py-3 px-10 rounded-full hover:bg-blue-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50"
            >
              Explore Pegasus 41
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

// Removed the simple CSS animation block as Framer Motion handles animations now.

export default NikePegasusPromoPage; 