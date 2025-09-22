import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- AF1 Image URLs (Focus on White, use Black for contrast if needed) ---
const heroImageUrl = `${backendAssetUrl}/images/AIR+FORCE+1+'07+(6).jpg`; // White side view
const featureImage1 = `${backendAssetUrl}/images/AIR+FORCE+1+'07+(4).jpg`; // White toe detail
const featureImage2 = `${backendAssetUrl}/images/AIR+FORCE+1+'07+(5).jpg`; // White heel detail
const featureImage3 = `${backendAssetUrl}/images/AIR+FORCE+1+'07+(2).jpg`; // White pair
const blackSideImageUrl = `${backendAssetUrl}/images/AIR+FORCE+1+'07+(7).jpg`; // Black side view (Optional use)


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

const NikeAF1PromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "30%"]);
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.5, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "50%"]);

  return (
    <div className="bg-gradient-to-b from-gray-100 to-white overflow-x-hidden"> {/* Classic light theme */}
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
        <div className="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-transparent z-10"></div> {/* Very subtle overlay */}

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-center justify-end pb-16 md:pb-24 h-full text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-4xl md:text-6xl lg:text-7xl font-extrabold mb-4 tracking-tighter text-gray-900 text-shadow-sm"
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            Nike Air Force 1 '07
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-light mb-8 max-w-2xl mx-auto text-gray-700"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            The Legend Lives On. Crisp Style, Enduring Comfort.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=sneakers" // Link to sneakers category
              className="bg-black text-white font-semibold py-3 px-8 rounded-md hover:bg-gray-800 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-black focus:ring-opacity-50"
            >
              Shop Air Force 1
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-gray-800 mb-6">An Icon Reimagined</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Introduced in 1982, the Air Force 1 redefined basketball footwear. Now an off-court staple, its legendary status continues with crisp leather, classic details, and all-day comfort thanks to Nike Air cushioning.
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
        {/* Feature 1: Premium Leather */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Crisp Leather Upper</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              Stitched leather overlays on the upper add heritage style, durability, and support. The clean lines and subtle details maintain the iconic look.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Durable and supportive</li>
              <li>Classic, versatile style</li>
              <li>Premium look and feel</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage1} alt="AF1 Toe Box Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: Nike Air Cushioning */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage2} alt="AF1 Heel Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Legendary Comfort</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              Originally designed for performance hoops, Nike Air cushioning adds lightweight, all-day comfort. The padded, low-cut collar looks sleek and feels great.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Nike Air technology</li>
              <li>Proven all-day comfort</li>
              <li>Sleek, padded collar</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: Durable Outsole */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Timeless Traction</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
             The rubber outsole with the heritage hoops pivot circle adds traction and durability, connecting you to the shoe's legendary history.
            </p>
             <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Durable rubber outsole</li>
              <li>Classic pivot circle pattern</li>
              <li>Reliable traction</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage3} alt="AF1 Pair" className="w-full h-auto object-contain aspect-[4/3]"/>
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
            className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
          >
            Step into an Icon
          </motion.h2>
          <motion.p
            className="text-lg text-gray-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Available in classic White, timeless Black, and more. Find the Air Force 1 that defines your style.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=sneakers" // Link back to sneakers category
              className="bg-white text-black font-semibold py-3 px-10 rounded-md hover:bg-gray-200 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50"
            >
              Discover AF1
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default NikeAF1PromoPage; 