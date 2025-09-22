import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Blundstone 550 Image URLs (Focus on Stout Brown, include Black) ---
const heroImageUrl = `${backendAssetUrl}/images/585_1_5.jpg`; // Stout Brown single
const featureImage1 = `${backendAssetUrl}/images/585_5_1.jpg`; // Stout Brown detail
const featureImage2 = `${backendAssetUrl}/images/585_om2m.jpg`; // Stout Brown on foot
const featureImage3 = `${backendAssetUrl}/images/587_m.jpg`; // Black on foot

// Animation Variants
const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.25, // Slightly more pronounced stagger
    },
  },
};

const itemVariants = {
  hidden: { opacity: 0, y: 25 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.7, ease: "easeOut" }
  },
};

const imageVariants = {
  hidden: { opacity: 0, filter: 'grayscale(50%)', y: 10 },
  visible: {
    opacity: 1,
    filter: 'grayscale(0%)',
    y: 0,
    transition: { duration: 1.0, ease: "easeOut" }
  },
};

const BlundstonePromoPage: React.FC = () => {
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
    <div className="bg-gradient-to-b from-stone-100 to-stone-200 overflow-x-hidden font-sans"> {/* Earthy, sturdy theme */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden">
        {/* Parallax Background Image */}
        <motion.div
          className="absolute inset-0 z-0 bg-stone-300"
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'cover', // Cover might be better for landscape/boot shots
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center center',
            y: backgroundY,
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent z-10"></div>

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-start justify-end pb-16 md:pb-24 h-full text-left p-6 md:p-12"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-4xl md:text-6xl lg:text-7xl font-bold mb-3 tracking-tight text-white text-shadow-md"
            initial={{ opacity: 0, x: -30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            Blundstone 550
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-light mb-8 max-w-xl text-gray-100 text-shadow-sm"
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            Everywhere Life Takes Me. Comfort and Durability, Redefined.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.9 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=boots" // Link to boots category
              className="bg-stone-800 text-white font-semibold py-3 px-8 rounded hover:bg-stone-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-opacity-50"
            >
              Shop Blundstone Boots
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-stone-800 mb-6">Legendary Comfort, Ready for Anything</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-stone-600 max-w-3xl mx-auto leading-relaxed">
          Experience the iconic Blundstone Chelsea boot. Crafted from premium leather, it's designed for ultimate comfort straight out of the box and built tough to handle wherever your day leads.
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
        {/* Feature 1: Premium Leather & Durability */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-stone-800 mb-4">Premium, Water-Resistant Leather</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
              Crafted from high-quality, water-resistant leather that looks great and stands up to the elements. Ready for work, play, and everything in between.
            </p>
            <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>Durable leather upper</li>
              <li>Water-resistant for versatility</li>
              <li>Classic, timeless look</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-2"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 200, damping: 15 }}
          >
            <img src={featureImage1} alt="Blundstone Stout Brown Detail" className="w-full h-auto object-contain aspect-square"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: Comfort Technology */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-white p-2"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 200, damping: 15 }}
          >
            <img src={featureImage2} alt="Blundstone Stout Brown On Foot" className="w-full h-auto object-contain aspect-square"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-bold text-stone-800 mb-4">Unrivaled Comfort</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
              Featuring Blundstone's SPS Max Comfort system with XRD® Technology in the heel strike zone for maximum shock absorption and all-day comfort.
            </p>
            <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>SPS Max Comfort system</li>
              <li>XRD® Technology for shock absorption</li>
              <li>Removable comfort footbed</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: Iconic Design */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-stone-800 mb-4">Effortless Style</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
             The classic Chelsea boot silhouette with iconic pull tabs makes for easy on/off. Versatile enough to pair with anything from rugged workwear to casual outfits.
            </p>
             <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>Classic Chelsea boot design</li>
              <li>Iconic pull tabs</li>
              <li>Versatile and stylish</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-2"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 200, damping: 15 }}
          >
            <img src={featureImage3} alt="Blundstone Black On Foot" className="w-full h-auto object-contain aspect-square"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-stone-800 text-white"
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
            Ready for Adventure?
          </motion.h2>
          <motion.p
            className="text-lg text-stone-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Discover the durability and comfort that has made Blundstone a global icon. Find your perfect pair today.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
             animate={{ opacity: 1, scale: 1 }}
             whileHover={{ scale: 1.05 }}
             whileTap={{ scale: 0.98 }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=boots" // Link back to boots category
              className="bg-white text-stone-800 font-semibold py-3 px-10 rounded hover:bg-stone-200 transition duration-300 ease-in-out shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Explore Blundstone Boots
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default BlundstonePromoPage; 