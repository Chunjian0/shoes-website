import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- G.H. Bass Larson Image URLs (Focus on Whiskey) ---
const heroImageUrl = `${backendAssetUrl}/images/GHBBA00823_65_BAZ1W002_WSK_HERO_3f0908f2-9523-4a0e-a347-00aa577aea0f.jpg`; // Whiskey hero
const featureImage1 = `${backendAssetUrl}/images/GHBBA00823_65_BAZ1W002_WSK_v5_1fa0697b-925d-4b4c-9698-02ae953ed279.jpg`; // Whiskey detail
const featureImage2 = `${backendAssetUrl}/images/GHBBA00823_65_BAZ1W002_WSK_on_b6f81b04-ab37-4e06-ab54-0cd61b4ff8f5.jpg`; // Whiskey on foot
const featureImage3 = `${backendAssetUrl}/images/GHBBA00324_15_BAZ1W002_DARKBROWN.jpg`; // Dark Brown pair (variation)

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
    transition: { duration: 0.7, ease: "easeOut" }
  },
};

const imageVariants = {
  hidden: { opacity: 0, scale: 0.95, filter: 'brightness(80%)' },
  visible: {
    opacity: 1,
    scale: 1,
    filter: 'brightness(100%)',
    transition: { duration: 0.9, ease: "easeOut" }
  },
};

const GHBassLarsonPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "15%"]);
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.6, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "30%"]);

  return (
    <div className="bg-gradient-to-b from-amber-50 via-white to-amber-100 overflow-x-hidden font-serif"> {/* Warm, classic theme */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[75vh] md:h-[90vh] overflow-hidden bg-amber-200">
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
        <div className="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-transparent z-10"></div>

        {/* Content */}
        <motion.div
          className="relative z-20 flex flex-col items-center justify-end pb-16 md:pb-24 h-full text-center p-4"
          style={{ y: contentY, opacity: contentOpacity }}
        >
          <motion.h1
            className="text-4xl md:text-6xl lg:text-7xl font-medium mb-3 tracking-normal text-stone-800"
            initial={{ opacity: 0, y: -30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          >
            G.H. Bass Larson Weejuns
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-normal mb-8 max-w-2xl mx-auto text-stone-700"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            The Original Penny Loafer. Timeless Style Since 1936.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=formal" // Link to formal category
              className="bg-red-800 text-white font-medium py-3 px-9 rounded hover:bg-red-900 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-opacity-50"
            >
              Shop Weejuns
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-semibold text-stone-800 mb-6">An Enduring Icon</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-stone-600 max-w-3xl mx-auto leading-relaxed">
          The quintessential penny loafer, the Larson Weejuns remains a staple of classic American style. Handcrafted with traditional moccasin construction and polished leather, it offers unparalleled versatility.
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
        {/* Feature 1: Handsewn Construction */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-semibold text-stone-800 mb-4">Genuine Handsewn Craft</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
              Each pair is meticulously crafted using traditional moccasin techniques, ensuring flexibility and a glove-like fit that molds to your foot over time.
            </p>
            <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>True moccasin construction</li>
              <li>Unmatched flexibility</li>
              <li>Personalized fit</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-3"
            variants={imageVariants}
            whileHover={{ scale: 1.03, boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)' }}
            transition={{ type: "spring", stiffness: 250, damping: 18 }}
          >
            <img src={featureImage1} alt="G.H. Bass Larson Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: Polished Leather */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-white p-3"
            variants={imageVariants}
            whileHover={{ scale: 1.03, boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)' }}
            transition={{ type: "spring", stiffness: 250, damping: 18 }}
          >
            <img src={featureImage2} alt="G.H. Bass Larson On Foot" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-semibold text-stone-800 mb-4">Refined Polished Leather</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
              The upper is crafted from high-quality polished leather, providing a sophisticated shine that elevates any outfit, from casual chinos to sharp tailoring.
            </p>
            <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>Premium polished leather</li>
              <li>Elegant sheen</li>
              <li>Easy to care for</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: Penny Keeper Detail */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-semibold text-stone-800 mb-4">The Original Penny Keeper</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
             Featuring the signature penny keeper strap detail – a nod to the shoe's rich history and enduring appeal. It's the mark of the genuine article.
            </p>
             <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>Iconic penny keeper strap</li>
              <li>Timeless design element</li>
              <li>Classic American style</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-3"
            variants={imageVariants}
            whileHover={{ scale: 1.03, boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)' }}
            transition={{ type: "spring", stiffness: 250, damping: 18 }}
          >
            <img src={featureImage3} alt="G.H. Bass Larson Dark Brown" className="w-full h-auto object-contain aspect-[4/3]"/>
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
            className="text-3xl md:text-4xl lg:text-5xl font-semibold mb-6 tracking-normal"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
          >
            Step into Tradition
          </motion.h2>
          <motion.p
            className="text-lg text-stone-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Own a piece of footwear history. The G.H. Bass Larson Weejuns is more than a shoe – it's a statement.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
             whileHover={{ scale: 1.05 }}
             whileTap={{ scale: 0.98 }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=formal" // Link back to formal category
              className="bg-white text-stone-800 font-semibold py-3 px-10 rounded hover:bg-stone-200 transition duration-300 ease-in-out shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Explore Loafers
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default GHBassLarsonPromoPage; 