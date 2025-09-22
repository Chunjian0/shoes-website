import React, { useRef } from 'react';
import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';

// --- Backend Asset URL Configuration ---
const backendAssetUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:2268';

// --- Allen Edmonds Strand Image URLs (Focus on Walnut) ---
const heroImageUrl = `${backendAssetUrl}/images/ec4000391_single_feed1000.jpg`; // Walnut single
const featureImage1 = `${backendAssetUrl}/images/ec4000391_detail_feed1000.jpg`; // Walnut detail
const featureImage2 = `${backendAssetUrl}/images/ec4000391_bottom_feed1000.jpg`; // Walnut bottom/sole
const featureImage3 = `${backendAssetUrl}/images/ec4001553_single_feed1000.jpg`; // Black single (for variation)

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
  hidden: { opacity: 0, scale: 0.95 },
  visible: {
    opacity: 1,
    scale: 1,
    transition: { duration: 0.9, ease: "easeOut" }
  },
};

const AllenEdmondsPromoPage: React.FC = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScrollYProgress } = useScroll({
    target: heroRef,
    offset: ["start start", "end start"],
  });

  // Parallax effect
  const backgroundY = useTransform(heroScrollYProgress, [0, 1], ["0%", "20%"]);
  const contentOpacity = useTransform(heroScrollYProgress, [0, 0.5, 1], [1, 1, 0]);
  const contentY = useTransform(heroScrollYProgress, [0, 1], ["0%", "30%"]);

  return (
    <div className="bg-gradient-to-b from-stone-50 to-stone-100 overflow-x-hidden font-serif"> {/* Elegant, classic theme */}
      {/* --- Hero Section --- */}
      <div ref={heroRef} className="relative h-[75vh] md:h-[95vh] overflow-hidden bg-stone-200">
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
        <div className="absolute inset-0 bg-gradient-to-t from-black/5 via-transparent to-transparent z-10"></div>

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
            Allen Edmonds Strand
          </motion.h1>
          <motion.p
            className="text-xl md:text-2xl font-normal mb-8 max-w-2xl mx-auto text-stone-600"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4, ease: "easeOut" }}
          >
            An American Original. Timeless Style. Handcrafted Quality.
          </motion.p>
          <motion.div
             initial={{ opacity: 0, scale: 0.8 }}
             animate={{ opacity: 1, scale: 1 }}
             transition={{ duration: 0.5, delay: 0.6, type: "spring", stiffness: 100 }}
          >
            <Link
              to="/products?category=formal" // Link to formal category
              className="bg-stone-800 text-white font-medium py-3 px-9 rounded hover:bg-stone-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-md focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-opacity-50"
            >
              Discover the Strand
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
        <motion.h2 variants={itemVariants} className="text-3xl md:text-4xl font-semibold text-stone-800 mb-6">The Epitome of Elegance</motion.h2>
        <motion.p variants={itemVariants} className="text-lg text-stone-600 max-w-3xl mx-auto leading-relaxed">
          A cornerstone of the sophisticated wardrobe, the Strand cap-toe oxford showcases intricate broguing and perforations. Handcrafted in the USA, it represents a legacy of quality and timeless design.
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
        {/* Feature 1: Handcrafted Quality */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-semibold text-stone-800 mb-4">Made in USA Craftsmanship</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
              Built on our classic 65 last, known for its elongated, elegant profile. Each pair is meticulously handcrafted in Port Washington, Wisconsin, using premium materials.
            </p>
            <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>Handcrafted in Port Washington, WI</li>
              <li>Premium European calfskin leather</li>
              <li>Built on the iconic 65 last</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-4"
            variants={imageVariants}
            whileHover={{ scale: 1.03, boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)' }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage1} alt="Allen Edmonds Strand Detail" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>

        {/* Feature 2: Goodyear Welt */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
          <motion.div
            className="md:w-1/2 group overflow-hidden rounded-lg shadow-xl bg-white p-4"
            variants={imageVariants}
            whileHover={{ scale: 1.03, boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)' }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage2} alt="Allen Edmonds Strand Sole" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
          <div className="md:w-1/2">
            <h3 className="text-2xl md:text-3xl font-semibold text-stone-800 mb-4">Built to Last</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
              Featuring a durable 360° Goodyear bench welt construction, the Strand is designed for longevity and can be recrafted by our experts, ensuring years of wear.
            </p>
            <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>360° Goodyear welt construction</li>
              <li>Recraftable for extended life</li>
              <li>Durable and supportive</li>
            </ul>
          </div>
        </motion.div>

        {/* Feature 3: Timeless Design */}
        <motion.div className="flex flex-col md:flex-row items-center gap-10 md:gap-16" variants={itemVariants}>
           <div className="md:w-1/2 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl font-semibold text-stone-800 mb-4">Classic Broguing</h3>
            <p className="text-stone-600 leading-relaxed mb-4">
             The signature cap-toe balmoral design is enhanced with intricate perforations and broguing details, offering a touch of classic sophistication for any occasion.
            </p>
             <ul className="list-disc list-inside text-stone-600 space-y-1">
              <li>Cap-toe balmoral oxford style</li>
              <li>Detailed perforations and broguing</li>
              <li>Versatile formal and business wear</li>
            </ul>
          </div>
          <motion.div
            className="md:w-1/2 order-1 md:order-2 group overflow-hidden rounded-lg shadow-xl bg-white p-4"
            variants={imageVariants}
            whileHover={{ scale: 1.03, boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)' }}
            transition={{ type: "spring", stiffness: 300, damping: 20 }}
          >
            <img src={featureImage3} alt="Allen Edmonds Strand Black" className="w-full h-auto object-contain aspect-[4/3]"/>
          </motion.div>
        </motion.div>
      </motion.div>

      {/* --- Call to Action Section --- */}
      <motion.div
        className="bg-stone-800 text-white" // Dark contrast section
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
            Invest in Enduring Style
          </motion.h2>
          <motion.p
            className="text-lg text-stone-300 max-w-2xl mx-auto mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            Experience the quality and timeless appeal of the Allen Edmonds Strand Oxford. A true investment piece.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.3, type: "spring", stiffness: 120 }}
          >
            <Link
              to="/products?category=formal" // Link back to formal category
              className="bg-white text-stone-800 font-semibold py-3 px-10 rounded hover:bg-stone-200 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
            >
              Explore Formal Shoes
            </Link>
           </motion.div>
        </div>
      </motion.div>
    </div>
  );
};

export default AllenEdmondsPromoPage; 