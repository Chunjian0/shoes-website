import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Adidas Superstar Image URLs (Focus on White/Green) ---
const heroImageUrl = `${backendAssetUrl}/images/SUPERSTAR_II_Green_JI3076_01_00_standard.jpg`; // Green side
const featureImage1 = `${backendAssetUrl}/images/SUPERSTAR_II_Green_JI3076_02_standard_hover.jpg`; // Green top hover
const featureImage2 = `${backendAssetUrl}/images/SUPERSTAR_II_Green_JI3076_03_standard.jpg`; // Green sole
const featureImage3 = `${backendAssetUrl}/images/SUPERSTAR_II_Blue_JI0145_01_00_standard.jpg`; // Blue side (for variation)

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

const AdidasSuperstarPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "20%"]);
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.5, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "35%"]);

  return (
    <div className="bg-white overflow-x-hidden"> {/* Clean white background */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden bg-gray-50"> {/* Light grey bg for contrast */}
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
        <div className="absolute inset-0 bg-gradient-to-t from-white/20 via-transparent to-transparent z-10"></div>

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
            Adidas Superstar
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-medium mb-8 max-w-2xl mx-auto text-gray-700"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            The Original Shell Toe. A Streetwear Legend.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=sneakers" // Link to sneakers category
              className="bg-blue-600 text-white font-semibold py-3 px-8 rounded-full hover:bg-blue-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50"
            >
              Shop Superstars
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-gray-800 mb-6">From Court to Street</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Born on the basketball courts in the '70s, the Superstar quickly transcended sport to become a staple of hip-hop culture and streetwear fashion. Its iconic shell toe design remains unchanged.
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
        {/* Feature 1: Shell Toe */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Iconic Shell Toe</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              The signature rubber shell toe provides distinctive style and durability. It's the defining feature that made the Superstar famous.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Instantly recognizable design</li>
              <li>Added protection</li>
              <li>Signature Superstar style</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-lg bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage1} alt="Adidas Superstar Shell Toe Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: Leather Upper & 3-Stripes */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-lg bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage3} alt="Adidas Superstar Blue Side" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Premium Feel</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              A smooth leather upper offers a classic look and comfortable feel. The serrated 3-Stripes branding adds authentic Adidas heritage.
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Smooth leather upper</li>
              <li>Serrated 3-Stripes detail</li>
              <li>Comfortable fit</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: Herringbone Outsole */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Reliable Grip</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
             The rubber outsole features a classic herringbone pattern, providing excellent traction and durability for everyday wear.
            </p>
             <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li>Durable rubber outsole</li>
              <li>Herringbone traction pattern</li>
              <li>Street-ready grip</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-lg bg-gray-50"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage2} alt="Adidas Superstar Sole" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-blue-700 text-white" // Adidas blue theme
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
            className="text-lg text-gray-100 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Still relevant, always classic. Find the Superstar that matches your style.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=sneakers" // Link back to sneakers category
              className="bg-white text-blue-700 font-semibold py-3 px-10 rounded-full hover:bg-gray-100 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Explore Superstar Collection
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default AdidasSuperstarPromoPage; 