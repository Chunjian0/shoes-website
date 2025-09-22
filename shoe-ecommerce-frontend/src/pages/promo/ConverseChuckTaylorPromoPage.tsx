import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Converse Chuck Taylor Image URLs (Using 0885 series) ---
const heroImageUrl = `${backendAssetUrl}/images/0885-CON568497C000009-1.jpg`; // Black side
const featureImage1 = `${backendAssetUrl}/images/0885-CON568497C000009-6.jpg`; // Black sole
const featureImage2 = `${backendAssetUrl}/images/0885-CON568498C000009-1.jpg`; // White side
const featureImage3 = `${backendAssetUrl}/images/0885-CON568497C000009-4.jpg`; // Black top view
const whitePairUrl = `${backendAssetUrl}/images/0885-CON568498C000009-3.jpg`; // White pair

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

const ConverseChuckTaylorPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "15%"]); // Subtle parallax
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.5, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "30%"]);

  return (
    <div className="bg-white overflow-x-hidden"> {/* Classic white background */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden bg-gray-100"> {/* Light grey bg for hero */}
        {/* Parallax Background Image */}
        <motion.div
          className="absolute inset-0 z-0"
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'contain',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center center',
            y: backgroundY,
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-white/10 via-transparent to-transparent z-10"></div>

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-center justify-end pb-16 md:pb-24 h-full text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-4xl md:text-6xl lg:text-7xl font-bold mb-4 tracking-tight text-black"
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            Chuck Taylor All Star
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-medium mb-8 max-w-2xl mx-auto text-gray-700"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            The Icon. Forever Classic.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=sneakers" // Link to sneakers category
              className="bg-red-600 text-white font-semibold py-3 px-8 rounded-full hover:bg-red-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-opacity-50"
            >
              Shop Chuck Taylors
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-gray-800 mb-6">The Undisputed Original</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Recognized worldwide, the Chuck Taylor All Star is a true cultural icon. Originally a basketball shoe, it has been adopted by rebels, artists, musicians, dreamers, thinkers and originals.
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
        {/* Feature 1: Canvas Upper */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Classic Canvas</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              The lightweight and durable canvas upper provides timeless style and breathable comfort. It's a blank canvas ready for your personal touch.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Lightweight and breathable</li>
              <li>Durable construction</li>
              <li>Timeless look</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-lg bg-gray-100"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage3} alt="Converse Chuck Taylor Top View" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: Vulcanized Rubber Sole */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-lg bg-gray-100"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage1} alt="Converse Chuck Taylor Sole" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Signature Sole</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              The vulcanized rubber outsole provides durability and traction. Its distinct diamond pattern is instantly recognizable.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Durable vulcanized rubber</li>
              <li>Diamond pattern tread</li>
              <li>Reliable traction</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: All Star Details */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Iconic Details</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
             From the unmistakable ankle patch (on high-tops) to the medial eyelets for airflow, every detail contributes to the Chuck Taylor's legendary status.
            </p>
             <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Star-centered ankle patch</li>
              <li>Medial eyelets for ventilation</li>
              <li>Classic silhouette</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-lg bg-gray-100"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage2} alt="Converse Chuck Taylor White Side" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-gray-800 text-white" // Darker section for contrast
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
            Find Your Chucks
          </motion.h2>
          <motion.p
            className="text-lg text-gray-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Available in countless colors and variations. Discover the pair that speaks to you.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=sneakers" // Link back to sneakers category
              className="bg-white text-black font-semibold py-3 px-10 rounded-full hover:bg-gray-200 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Explore All Sneakers
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default ConverseChuckTaylorPromoPage; 