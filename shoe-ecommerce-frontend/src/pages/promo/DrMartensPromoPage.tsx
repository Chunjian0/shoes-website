import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Dr. Martens 1460 Image URLs (Focus on Black Smooth) ---
const heroImageUrl = `${backendAssetUrl}/images/11822006.jpg`; // Black side view
const featureImage1 = `${backendAssetUrl}/images/11822006 (7).jpg`; // Black sole detail
const featureImage2 = `${backendAssetUrl}/images/11822006 (1).jpg`; // Black laces detail
const featureImage3 = `${backendAssetUrl}/images/11822006 (4).jpg`; // Black pair
const cherryRedImageUrl = `${backendAssetUrl}/images/11822600.jpg`; // Cherry Red side view (Optional)
const whiteImageUrl = `${backendAssetUrl}/images/11822100.jpg`;     // White side view (Optional)

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

const DrMartensPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "20%"]); // Less intense parallax for boots?
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.5, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "40%"]);

  return (
    <div className="bg-gradient-to-b from-gray-100 to-white overflow-x-hidden"> {/* Light bg, classic feel */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden">
        {/* Parallax Background */}
        <motion.div
          className="absolute inset-0 z-0 bg-white"
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'contain',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center center',
            y: backgroundY,
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/5 via-transparent to-transparent z-10"></div>

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-center justify-end pb-16 md:pb-24 h-full text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-4xl md:text-6xl lg:text-7xl font-extrabold mb-4 tracking-tighter text-black text-shadow"
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            Dr. Martens 1460
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-light mb-8 max-w-2xl mx-auto text-gray-700"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            The Original. Built To Last. An Icon of Self-Expression.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=boots" // Link to boots category
              className="bg-yellow-400 text-black font-semibold py-3 px-8 rounded hover:bg-yellow-300 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50"
            >
              Shop 1460 Boots
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Instantly Recognizable</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Born on 01.04.60. Named the 1460. Over six decades, our 8-eye work boot has become iconic. Crafted with durable Smooth leather, signature yellow stitching, and the comfortable AirWair™ sole, it's built for rebellion.
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
        {/* Feature 1: Smooth Leather */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Durable Smooth Leather</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              Famously stiff to start, our Smooth leather can be polished to a dapper shine or artfully scuffed-up depending on your preference. It molds to your feet over time.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Hardwearing and notoriously tough</li>
              <li>Molds to your foot</li>
              <li>Polish or scuff to personalize</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage1} alt="Dr Martens Sole Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: AirWair Sole & Goodyear Welt */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage2} alt="Dr Martens Laces Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Built Different</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              Our Goodyear-welted lines are heat-sealed at 700°C and reinforced with our signature welt stitch. The original air-cushioned sole provides comfort and is resistant to oil, fat, abrasion, and slip.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Goodyear-welted construction</li>
              <li>Signature yellow stitching</li>
              <li>Air-cushioned, durable sole</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: 8-Eye Icon */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Unmistakable DNA</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
             Instantly recognizable 8-eye silhouette, grooved sides, a scripted heel-loop, and that iconic yellow stitching. It's the foundation of decades of counter-culture style.
            </p>
             <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>8-eye lace-up design</li>
              <li>Scripted heel loop</li>
              <li>Iconic silhouette</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage3} alt="Dr Martens Pair" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-black text-white" // Contrast section
        initial={{ opacity: 0 }}
        whileInView={{ opacity: 1 }}
        viewport={{ once: true, amount: 0.5 }}
        transition={{ duration: 0.8 }}
      >
        <div className="container mx-auto px-6 py-20 md:py-28 text-center">
          <motion.h2
            className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6 text-yellow-400"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
          >
            Lace Up Your Attitude
          </motion.h2>
          <motion.p
            className="text-lg text-gray-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Choose your color, break them in, make them yours. The 1460 boot is a blank canvas for self-expression.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=boots" // Link back to boots category
              className="bg-yellow-400 text-black font-semibold py-3 px-10 rounded hover:bg-yellow-300 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50"
            >
              Shop Dr. Martens
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default DrMartensPromoPage; 