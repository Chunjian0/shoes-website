import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Crocs Classic Clog Image URLs (Focus on Butter & Atmosphere) ---
const heroImageUrl = `${backendAssetUrl}/images/10001_78Z_2.jpg`; // Butter pair
const featureImage1 = `${backendAssetUrl}/images/10001_78Z_3.jpg`; // Butter top pair
const featureImage2 = `${backendAssetUrl}/images/10001_4WK_1.jpg`; // Atmosphere side
const featureImage3 = `${backendAssetUrl}/images/10001_4WK_3.jpg`; // Atmosphere top pair

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
  hidden: { opacity: 0, scale: 0.8, rotate: -5 },
  visible: {
    opacity: 1,
    scale: 1,
    rotate: 0,
    transition: { duration: 0.8, ease: "easeOut" }
  },
};

const CrocsClassicClogPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect - More playful
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "-10%"]); // Move up slightly
  const backgroundRotate = useTransform(heroScrollYProgress, [0, 1], [0, 5]); // Rotate slightly
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.6, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "30%"]);

  return (
    <div className="bg-gradient-to-br from-yellow-100 via-cyan-100 to-pink-100 overflow-x-hidden"> {/* Fun gradient */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden">
        {/* Background Image */}
        <motion.div
          className="absolute inset-0 z-0"
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'contain',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center center',
            y: backgroundY,
            rotate: backgroundRotate,
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-white/10 via-transparent to-transparent z-10"></div>

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-center justify-center h-full text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-5xl md:text-7xl lg:text-8xl font-extrabold mb-4 tracking-tight text-purple-600 drop-shadow-lg"
            initial={{ opacity: 0, scale: 0.5 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.8, delay: 0.2, type: "spring", stiffness: 100 }}
          >
            Crocs Classic Clog
          </motion.h1>
          <motion.p
            className="text-xl md:text-3xl font-bold mb-8 max-w-2xl mx-auto text-cyan-700 drop-shadow-sm"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            Comfort That's All Your Own.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, y: 30 }}
             animate={{ opacity: 1, y: 0 }}
             transition={{ duration: 0.5, delay: 0.6, ease: "easeOut" }}
          >
            <Link
              to="/products?category=sandals" // Link to sandals category
              className="bg-pink-500 text-white font-bold py-4 px-10 rounded-full hover:bg-pink-600 transition duration-300 ease-in-out transform hover:scale-110 hover:rotate-[-2deg] shadow-xl focus:outline-none focus:ring-2 focus:ring-pink-300 focus:ring-opacity-70 text-lg"
            >
              Find Your Fun!
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-5xl font-bold text-gray-800 mb-6">Lightweight & Lovable</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
          The original. The icon. The clog that started a comfort revolution around the world! Incredibly light, fun to wear, and endlessly customizable. It's the perfect invitation to be comfortable in your own shoes.
        </motion.p>
      </motion.div>

      {/* --- Features Section (Icon Grid) --- */}
      <motion.div
        className="container mx-auto px-6 py-16 md:py-24"
        variants={containerVariants}
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
      >
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 md:gap-12">
          {/* Feature 1: Iconic Comfort */}
          <motion.div className="text-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300" variants={itemVariants}>
            <motion.div className="text-6xl mb-4 text-green-500" whileHover={{ scale: 1.2, rotate: 10 }}>🐊</motion.div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">Iconic Crocs Comfort™</h3>
            <p className="text-gray-600 text-sm">Lightweight, flexible, 360-degree comfort thanks to Croslite™ material.</p>
          </motion.div>

          {/* Feature 2: Ventilation */}
          <motion.div className="text-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300" variants={itemVariants}>
             <motion.div className="text-6xl mb-4 text-blue-500" whileHover={{ scale: 1.2, rotate: -10 }}>💨</motion.div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">Breathable & Water-Friendly</h3>
            <p className="text-gray-600 text-sm">Ventilation ports add breathability, help shed water and debris quickly.</p>
          </motion.div>

          {/* Feature 3: Easy Clean */}
          <motion.div className="text-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300" variants={itemVariants}>
             <motion.div className="text-6xl mb-4 text-yellow-500" whileHover={{ scale: 1.2, rotate: 10 }}>🧼</motion.div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">Easy to Clean</h3>
            <p className="text-gray-600 text-sm">Simply rinse with soap and water. Quick to dry!</p>
          </motion.div>

          {/* Feature 4: Customizable */}
          <motion.div className="text-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300" variants={itemVariants}>
             <motion.div className="text-6xl mb-4 text-red-500" whileHover={{ scale: 1.2, rotate: -10 }}>🎨</motion.div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">Make Them Yours</h3>
            <p className="text-gray-600 text-sm">Pivoting heel straps for a more secure fit, plus customizable with Jibbitz™ charms.</p>
          </motion.div>
        </div>

        {/* Image Showcase */}
        <motion.div className="mt-20 grid grid-cols-1 sm:grid-cols-2 gap-8 items-center">
             <motion.div className="rounded-lg overflow-hidden shadow-xl" variants={imageVariants}>
                 <img src={featureImage2} alt="Crocs Atmosphere Side" className="w-full h-auto object-cover"/>
             </motion.div>
             <motion.div className="rounded-lg overflow-hidden shadow-xl" variants={imageVariants}>
                 <img src={featureImage3} alt="Crocs Atmosphere Top Pair" className="w-full h-auto object-cover"/>
             </motion.div>
         </motion.div>

      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-purple-500 text-white" // Fun purple
        initial={{ opacity: 0, y: 50 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true, amount: 0.5 }}
        transition={{ duration: 0.8, ease: "easeOut" }}
      >
        <div className="container mx-auto px-6 py-20 md:py-28 text-center">
          <motion.h2
            className="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 drop-shadow-md"
            initial={{ opacity: 0, scale: 0.7 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1, type: "spring" }}
          >
            Come As You Are™
          </motion.h2>
          <motion.p
            className="text-lg text-purple-100 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.3 }}
          >
            Find your favorite color, add some Jibbitz™, and express yourself! What are you waiting for?
          </motion.p>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
             whileHover={{ scale: 1.1 }}
             whileTap={{ scale: 0.95 }}
            transition={{ duration: 0.5, delay: 0.4, type: "spring", stiffness: 150 }}
          >
            <Link
              to="/products?category=sandals" // Link back to sandals category
              className="bg-white text-purple-600 font-bold py-4 px-12 rounded-full hover:bg-gray-100 transition duration-300 ease-in-out shadow-lg text-xl"
            >
              Shop Classic Clogs
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default CrocsClassicClogPromoPage; 