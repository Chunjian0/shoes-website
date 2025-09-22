import React from 'react';
import { motion } from 'framer-motion';
import { FiTruck, FiGlobe, FiClock, FiMapPin, FiPackage, FiHelpCircle } from 'react-icons/fi';

// Animation variants for sections
const sectionVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: (i: number) => ({
    opacity: 1,
    y: 0,
    transition: {
      delay: i * 0.15, // Stagger animation based on index
      duration: 0.5,
      ease: 'easeOut'
    }
  })
};

interface PolicySectionProps {
  icon: React.ElementType;
  title: string;
  children: React.ReactNode;
  custom: number; // Index for stagger animation
}

const PolicySection: React.FC<PolicySectionProps> = ({ icon: Icon, title, children, custom }) => {
  return (
    <motion.div
      className="bg-white p-8 rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-300"
      variants={sectionVariants}
      initial="hidden"
      animate="visible"
      custom={custom} // Pass index to variants
    >
      <div className="flex items-center mb-4">
        <Icon className="w-8 h-8 text-indigo-600 mr-4 flex-shrink-0" />
        <h2 className="text-2xl font-semibold text-gray-900">{title}</h2>
      </div>
      <div className="text-gray-700 leading-relaxed space-y-3">
        {children}
      </div>
    </motion.div>
  );
};

const ShippingPolicyPage: React.FC = () => {
  const sections = [
    {
      icon: FiClock,
      title: "Order Processing",
      content: (
        <>
          <p>Most orders are processed and shipped within 1-2 business days (Monday - Friday, excluding holidays). You will receive a shipping confirmation email with tracking information once your order leaves our warehouse.</p>
          <p>During peak seasons or sales events, processing times may be slightly longer. We appreciate your patience!</p>
        </>
      )
    },
    {
      icon: FiTruck,
      title: "Domestic Shipping (USA)",
      content: (
        <>
          <p>We offer the following shipping options within the United States:</p>
          <ul className="list-disc list-inside space-y-1 pl-4">
            <li><strong>Standard Shipping:</strong> Estimated delivery within 3-7 business days. Costs $5.99 or is FREE on orders over $75.</li>
            <li><strong>Expedited Shipping:</strong> Estimated delivery within 2-3 business days. Cost calculated at checkout based on weight and location.</li>
          </ul>
          <p>Delivery times are estimates and may vary based on carrier delays or your location.</p>
        </>
      )
    },
    {
      icon: FiGlobe,
      title: "International Shipping",
      content: (
        <>
          <p>Yes, we ship internationally to most countries! Available destinations and shipping options will be presented during checkout.</p>
          <p>International shipping costs are calculated based on the destination, package weight, and selected service. Estimated delivery times typically range from 7-21 business days but can vary significantly.</p>
          <p><strong>Important:</strong> International customers are responsible for any customs duties, taxes, or import fees levied by their country. These charges are not included in the shipping cost paid to us.</p>
        </>
      )
    },
    {
      icon: FiMapPin,
      title: "Order Tracking",
      content: (
        <>
          <p>Once your order ships, you'll receive an email containing your tracking number. You can use this number on the carrier's website (e.g., UPS, FedEx, USPS) to follow your package's journey.</p>
          <p>You can also find tracking information in your account dashboard under "Order History" if you created an account.</p>
        </>
      )
    },
    {
      icon: FiPackage,
      title: "Shipping Restrictions & Issues",
      content: (
        <>
          <p>We currently cannot ship to P.O. Boxes or APO/FPO addresses. Please provide a physical street address for delivery.</p>
          <p>Please ensure your shipping address is correct before placing your order. We are not responsible for orders shipped to incorrectly provided addresses.</p>
          <p>If your package is lost or arrives damaged, please contact our customer support team immediately with your order number so we can assist you.</p>
        </>
      )
    },
    {
      icon: FiHelpCircle,
      title: "Questions?",
      content: (
        <>
          <p>If you have any further questions about our shipping policy, please don't hesitate to contact our customer support team. We're here to help!</p>
          <p>You can reach us via our <a href="/contact" className="text-indigo-600 hover:text-indigo-800 underline font-medium">Contact Page</a>.</p>
        </>
      )
    }
  ];

  return (
    <div className="bg-gradient-to-b from-indigo-50 via-white to-gray-50 min-h-screen py-16 sm:py-24">
      <motion.div
        className="container mx-auto px-4 sm:px-6 lg:px-8"
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6, ease: "easeOut" }}
      >
        {/* Header Section */}
        <div className="text-center mb-16">
          <motion.h1
            className="text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 mb-4"
            initial={{ scale: 0.9, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ delay: 0.2, duration: 0.5, type: "spring", stiffness: 100 }}
          >
            Shipping Policy
          </motion.h1>
          <motion.p
            className="mt-4 text-lg leading-6 text-gray-600 max-w-3xl mx-auto"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.4, duration: 0.5 }}
          >
            Everything you need to know about how we get your new favorite shoes to your doorstep. Fast, reliable, and clear.
          </motion.p>
        </div>

        {/* Policy Sections */}
        <div className="max-w-4xl mx-auto grid gap-8 md:grid-cols-1 lg:gap-10">
          {sections.map((section, index) => (
            <PolicySection
              key={section.title}
              icon={section.icon}
              title={section.title}
              custom={index} // Pass index for stagger effect
            >
              {section.content}
            </PolicySection>
          ))}
        </div>

      </motion.div>
    </div>
  );
};

export default ShippingPolicyPage; 