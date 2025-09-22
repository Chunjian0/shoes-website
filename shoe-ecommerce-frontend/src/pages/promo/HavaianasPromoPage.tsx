import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Havaianas Top Image URLs (Focus on White, Blue) ---
const heroImageUrl = `${backendAssetUrl}/images/4000029_0001_A__2.jpg`; // White side angle
const featureImage1 = `${backendAssetUrl}/images/4000029_0001_C__2.jpg`; // White top pair
const featureImage2 = `${backendAssetUrl}/images/4000029_2711_A__2_2fb28641-4cb2-4f66-9dfd-591b8d58a423.jpg`; // Blue side angle
const featureImage3 = `${backendAssetUrl}/images/4000029_2711_C__2_6c543ef7-1cf2-47ca-a238-3370627f9640.jpg`; // Blue top pair

// Animation Variants
const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.2,
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
    transition: { duration: 0.8, ease: "easeOut" }
  },
};

const HavaianasPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "10%"]); // Very subtle parallax
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.5, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "25%"]);

  return (
    <div className="bg-gradient-to-b from-sky-100 via-white to-yellow-50 overflow-x-hidden"> {/* Beachy gradient */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[70vh] md:h-[90vh] overflow-hidden"> {/* Slightly shorter hero */}
        {/* Parallax Background Image */}
        <motion.div
          className="absolute inset-0 z-0"
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'contain',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center bottom', // Position at bottom for flip-flop
            y: backgroundY,
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-white/30 via-transparent to-transparent z-10"></div>

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-center justify-center h-full text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-4xl md:text-6xl lg:text-7xl font-bold mb-4 tracking-tight text-blue-600"
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            Havaianas Top
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-semibold mb-8 max-w-2xl mx-auto text-gray-700"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            The Original. The Best. Essential Summer Comfort.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=sandals" // Link to sandals category
              className="bg-yellow-400 text-black font-semibold py-3 px-8 rounded-full hover:bg-yellow-500 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-opacity-50"
            >
              Shop Havaianas
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Pure Brazilian Spirit</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Simple, versatile, and incredibly comfortable. The Havaianas Top flip-flop is the embodiment of Brazil's vibrant and relaxed lifestyle. Perfect for the beach, the pool, or just everyday wear.
        </motion.p>
      </motion.div>

      {/* --- Features Section (Simple Grid) --- */}
      <motion.div
        className="container mx-auto px-6 py-16 md:py-24"
        variants={containerVariants}
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
      >
        <div className="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-16 items-start">
          {/* Feature 1: Comfort */}
          <motion.div className="text-center" variants={itemVariants}>
             <motion.div
                className="mx-auto mb-6 w-48 h-32 group overflow-hidden rounded-lg shadow bg-white flex items-center justify-center"
                variants={imageVariants}
                whileHover={{ scale: 1.05 }}
                transition={{ type: "spring", stiffness: 300 }}
             >
                 <img src={featureImage1} alt="Havaianas White Top Pair" className="w-full h-auto object-contain p-4"/>
             </motion.div>
             <h3 className="text-xl font-semibold text-gray-800 mb-3">Signature Comfort</h3>
             <p className="text-gray-600 leading-relaxed">
               Featuring the unique textured rice pattern footbed, designed for ultimate comfort and a gentle massage effect.
             </p>
          </motion.div>

          {/* Feature 2: Durable & Water-Resistant */}
          <motion.div className="text-center" variants={itemVariants}>
              <motion.div
                className="mx-auto mb-6 w-48 h-32 group overflow-hidden rounded-lg shadow bg-white flex items-center justify-center"
                variants={imageVariants}
                whileHover={{ scale: 1.05 }}
                transition={{ type: "spring", stiffness: 300 }}
             >
                 <img src={featureImage2} alt="Havaianas Blue Side Angle" className="w-full h-auto object-contain p-4"/>
             </motion.div>
             <h3 className="text-xl font-semibold text-gray-800 mb-3">Built for Summer</h3>
             <p className="text-gray-600 leading-relaxed">
               Made from durable, water-resistant rubber. They're lightweight, heat-resistant, and non-slip – perfect for any adventure.
             </p>
          </motion.div>

          {/* Feature 3: Vibrant Colors */}
          <motion.div className="text-center" variants={itemVariants}>
              <motion.div
                className="mx-auto mb-6 w-48 h-32 group overflow-hidden rounded-lg shadow bg-white flex items-center justify-center"
                variants={imageVariants}
                whileHover={{ scale: 1.05 }}
                transition={{ type: "spring", stiffness: 300 }}
             >
                 <img src={featureImage3} alt="Havaianas Blue Top Pair" className="w-full h-auto object-contain p-4"/>
             </motion.div>
             <h3 className="text-xl font-semibold text-gray-800 mb-3">Endless Colors</h3>
             <p className="text-gray-600 leading-relaxed">
               Available in a rainbow of vibrant colors to match your mood, your outfit, or your destination.
             </p>
          </motion.div>
        </div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-blue-500 text-white" // Bright blue
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
            Step into Summer
          </motion.h2>
          <motion.p
            className="text-lg text-gray-50 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Grab your pair of Havaianas Top and feel the sunshine wherever you go.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=sandals" // Link back to sandals category
              className="bg-white text-blue-500 font-semibold py-3 px-10 rounded-full hover:bg-gray-100 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Explore All Sandals
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default HavaianasPromoPage; 