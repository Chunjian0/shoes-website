import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Adios Pro 3 Image URLs (Black/Carbon/Solar Green Colorway) ---
const heroImageUrl = `${backendAssetUrl}/images/ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM1.jpg`;
const featureImage1 = `${backendAssetUrl}/images/ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM3_hover.jpg`; // Angle 1
const featureImage2 = `${backendAssetUrl}/images/ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM4.jpg`; // Angle 2
const featureImage3 = `${backendAssetUrl}/images/ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM8.jpg`; // Pair 3
// Add more specific feature images if needed from the list (HM5, HM6, HM7)

// Animation Variants (Can be reused or customized)
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

const AdidasAdiosProPromoPage: React.FC = () => {
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
    <div className="bg-gradient-to-b from-gray-800 to-black overflow-x-hidden text-white"> {/* Darker theme */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[80vh] md:h-screen overflow-hidden">
        {/* Parallax Background */}
        <motion.div
          className="absolute inset-0 z-0 bg-black" // Dark background
          style={{
            backgroundImage: `url(${heroImageUrl})`,
            backgroundSize: 'contain',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'center center',
            y: backgroundY,
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent z-10"></div>

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-center justify-end pb-16 md:pb-24 h-full text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-4xl md:text-6xl lg:text-7xl font-extrabold mb-4 tracking-tighter text-shadow-lg" // Stronger shadow on dark bg
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            Adidas Adizero Adios Pro 3
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-light mb-8 max-w-2xl mx-auto text-gray-200 text-shadow-sm" // Lighter text color
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            Engineered for Speed. Built for Records. Experience the pinnacle of racing innovation.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=running" // Link to running category
              className="bg-lime-400 text-black font-semibold py-3 px-8 rounded-full hover:bg-lime-300 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-lime-200 focus:ring-opacity-50" // Lime accent color
            >
              Shop Adizero
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-bold text-gray-100 mb-6">Push Your Limits</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-gray-400 max-w-3xl mx-auto leading-relaxed">
          The Adizero Adios Pro 3 is meticulously crafted for runners chasing their next personal best. Featuring groundbreaking technology, it offers optimized cushioning, explosive energy return, and race-day stability.
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
        {/* Feature 1: Lightstrike Pro */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-100 mb-4">Dual Lightstrike Pro Foam</h3>
            <p className="text-gray-400 leading-relaxed mb-4">
              Two layers of resilient Lightstrike Pro foam create a midsole that provides unparalleled cushioning and maximum energy return, keeping you feeling energized lap after lap.
            </p>
            <ul className="list-disc list-inside text-gray-400 space-y-1">
              <li>Optimized race cushioning</li>
              <li>Highest energy return</li>
              <li>Lightweight and responsive</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-900" // Darker bg for image frame
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage1} alt="Adios Pro 3 Angled View" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: ENERGYRODS 2.0 */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-gray-900"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage2} alt="Adios Pro 3 Side Profile" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-100 mb-4">ENERGYRODS 2.0</h3>
            <p className="text-gray-400 leading-relaxed mb-4">
              Carbon-infused ENERGYRODS 2.0, embedded in the midsole, provide lightweight stiffness. They deliver a unified system for harmonious stiffness and explosive energy return.
            </p>
            <ul className="list-disc list-inside text-gray-400 space-y-1">
              <li>Anatomically driven stiffness</li>
              <li>Enhanced running economy</li>
              <li>Propulsive ride</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: Continental™ Outsole */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-bold text-gray-100 mb-4">Continental™ Grip</h3>
            <p className="text-gray-400 leading-relaxed mb-4">
             A thin layer of textile rubber outsole featuring Continental™ rubber provides extraordinary grip on wet and dry surfaces, allowing you to take corners with confidence.
            </p>
             <ul className="list-disc list-inside text-gray-400 space-y-1">
              <li>Premium traction</li>
              <li>Reliable grip in all conditions</li>
              <li>Durable and lightweight</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-gray-900"
            variants={imageVariants}
            whileHover={{ scale: 1.03 }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage3} alt="Adios Pro 3 Pair" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-gray-900 text-white" // Consistent dark background
        initial={{ opacity: 0 }}
        whileInView={{ opacity: 1 }}
        viewport={{ once: true, amount: 0.5 }}
        transition={{ duration: 0.8 }}
      >
        <div className="container mx-auto px-6 py-20 md:py-28 text-center">
          <motion.h2
            className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6 text-gray-100"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
          >
            Ready to Shatter Records?
          </motion.h2>
          <motion.p
            className="text-lg text-gray-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Unleash your potential with the Adidas Adizero Adios Pro 3. Find yours today.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=running" // Link back to running category
              className="bg-lime-400 text-black font-semibold py-3 px-10 rounded-full hover:bg-lime-300 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-lime-200 focus:ring-opacity-50" // Lime accent color
            >
              Explore Adios Pro 3
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default AdidasAdiosProPromoPage; 