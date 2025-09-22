import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- ASICS GEL-Kayano 30 Image URLs (Focus on Island Blue) ---
const heroImageUrl = `${backendAssetUrl}/images/1011B548_404_SR_LT_GLB.jpg`; // Blue Left Side
const featureImage1 = `${backendAssetUrl}/images/1011B548_404_SB_FR_GLB.jpg`; // Blue Pair Front
const featureImage2 = `${backendAssetUrl}/images/1011B548_404_SB_BK_GLB.jpg`; // Blue Back
const featureImage3 = `${backendAssetUrl}/images/1011B548_404_SB_BT_GLB.jpg`; // Blue Sole

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
  hidden: { opacity: 0, y: 25 }, // Slightly increased y offset
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { duration: 0.7, ease: "easeOut" } // Replaced custom cubic-bezier
  },
};

const imageVariants = {
  hidden: { opacity: 0, scale: 0.92, filter: 'blur(5px)' }, // Added blur effect
  visible: {
    opacity: 1,
    scale: 1,
    filter: 'blur(0px)',
    transition: { duration: 0.9, ease: "easeOut" } // Replaced custom cubic-bezier
  },
};

const AsicsGelKayanoPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"], 
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "25%"]); 
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.6, 1], [1, 1, 0]); 
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "40%"]);

  return (
    <div className="bg-gradient-to-b from-sky-50 via-white to-sky-100 overflow-x-hidden"> {/* Asics Blue Theme */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden bg-sky-100">
        {/* Parallax Background */}
        <motion.div
          className="absolute inset-0 z-0" 
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'contain', 
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center 80%', // Position image lower
            y: backgroundY, 
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/15 via-transparent to-transparent z-10"></div> 
        
        {/* Content */}
        <motion.div 
          className="relative z-20 flex flex-col items-center justify-end pb-16 md:pb-24 h-full text-white text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }} 
        >
          <motion.h1 
            className="text-4xl md:text-6xl lg:text-7xl font-bold mb-3 tracking-tight text-shadow-md" // Tighter tracking
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            ASICS GEL-Kayano 30
          </motion.h1>
          <motion.p 
            className="text-xl md:text-2xl font-light mb-8 max-w-2xl mx-auto text-shadow" // Slightly smaller shadow
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            Adaptive Stability. Premium Comfort. Engineered for You.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 110 }}
          >
            <Link 
              to="/products?category=running" // Link to running category
              className="bg-white text-sky-700 font-semibold py-3 px-9 rounded-full hover:bg-sky-100 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-60"
            >
              Discover Kayano 30
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
        viewport={{ once: true, amount: 0.3 }} 
      >
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-semibold text-sky-900 mb-6">Stability Redefined</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-700 max-w-3xl mx-auto leading-relaxed">
          The GEL-Kayano 30 introduces the revolutionary 4D GUIDANCE SYSTEM™, offering intelligent stability that adapts to your stride. Combined with PureGEL™ technology and FF BLAST™ PLUS ECO cushioning, it delivers unprecedented comfort and support.
        </motion.p>
      </motion.div>

      {/* --- Features Section (Alternating Layout) --- */}
      <motion.div 
        className="container mx-auto px-6 py-16 md:py-24 space-y-20 md:space-y-28" 
        variants={containerVariants} 
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }} 
      >
        {/* Feature 1: 4D GUIDANCE SYSTEM™ */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}> 
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-semibold text-sky-900 mb-4">4D GUIDANCE SYSTEM™</h3>
            <p className="text-gray-700 leading-relaxed mb-4">
             This integrated system provides adaptive stability, guiding your foot through every step for a smoother, more efficient run, especially when fatigue sets in.
            </p>
            <ul className="list-disc list-inside text-gray-700 space-y-1">
              <li>Adaptive support</li>
              <li>Reduces pronation</li>
              <li>Enhanced running efficiency</li>
            </ul>
          </div>
          <motion.div 
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-3" 
            variants={imageVariants} 
            whileHover={{ scale: 1.04, boxShadow: '0 10px 20px rgba(30, 144, 255, 0.1)' }} // Asics blue shadow
            transition={{ type: "spring", stiffness: 280, damping: 18 }}
          >
            <img src={featureImage1} alt="Kayano 30 4D Guidance System Visualization" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: PureGEL & FF BLAST PLUS ECO */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div 
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-white p-3"
            variants={imageVariants}
            whileHover={{ scale: 1.04, boxShadow: '0 10px 20px rgba(30, 144, 255, 0.1)' }}
            transition={{ type: "spring", stiffness: 280, damping: 18 }}
          >
            <img src={featureImage2} alt="Kayano 30 Heel showing PureGEL" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-semibold text-sky-900 mb-4">Cloud-Like Comfort</h3>
            <p className="text-gray-700 leading-relaxed mb-4">
              Experience softer landings with new PureGEL™ technology strategically placed in the heel. The FF BLAST™ PLUS ECO cushioning, made with bio-based content, adds lightweight, responsive comfort underfoot.
            </p>
            <ul className="list-disc list-inside text-gray-700 space-y-1">
              <li>PureGEL™ for shock absorption</li>
              <li>FF BLAST™ PLUS ECO cushioning</li>
              <li>Lightweight and responsive feel</li>
            </ul>
          </div>
        </motion.div>
        
        {/* Feature 3: Engineered Knit Upper */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-semibold text-sky-900 mb-4">Breathable & Supportive Fit</h3>
            <p className="text-gray-700 leading-relaxed mb-4">
             The engineered stretch knit upper wraps your foot comfortably, providing improved breathability and a secure foothold without sacrificing flexibility.
            </p>
             <ul className="list-disc list-inside text-gray-700 space-y-1">
              <li>Soft engineered knit</li>
              <li>Excellent ventilation</li>
              <li>Secure, adaptive fit</li>
            </ul>
          </div>
          <motion.div 
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-3"
            variants={imageVariants}
            whileHover={{ scale: 1.04, boxShadow: '0 10px 20px rgba(30, 144, 255, 0.1)' }}
            transition={{ type: "spring", stiffness: 280, damping: 18 }}
          >
            <img src={featureImage3} alt="Kayano 30 Sole Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div 
        className="bg-sky-900 text-white" // Darker blue background
        initial={{ opacity: 0 }}
        whileInView={{ opacity: 1 }}
        viewport={{ once: true, amount: 0.5 }}
        transition={{ duration: 0.8 }}
      >
        <div className="container mx-auto px-6 py-20 md:py-28 text-center">
          <motion.h2 
            className="text-3xl md:text-4xl lg:text-5xl font-semibold mb-6 tracking-tight"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
          >
            Find Your Smoothest Run
          </motion.h2>
          <motion.p 
            className="text-lg text-sky-200 max-w-2xl mx-auto mb-10" // Lighter text color
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Discover the ASICS GEL-Kayano 30 and unlock a new level of stable comfort on your runs.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            whileHover={{ scale: 1.06, transition: { type: 'spring', stiffness: 150 } }} // Springy hover
            whileTap={{ scale: 0.98 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link 
              to="/products?category=running&brand=ASICS" // Link to ASICS running shoes
              className="bg-white text-sky-800 font-bold py-3 px-10 rounded-full hover:bg-sky-100 transition duration-300 ease-in-out shadow-xl focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Shop ASICS Running
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default AsicsGelKayanoPromoPage; 