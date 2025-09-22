import React from 'react';
import { motion } from 'framer-motion';
import { FiRotateCcw, FiCheckSquare, FiXSquare, FiCreditCard, FiPackage, FiHelpCircle, FiCalendar, FiList } from 'react-icons/fi';
import { Link } from 'react-router-dom'; // Import Link for internal navigation

// Animation variants for sections (reusable)
const sectionVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: (i: number) => ({
    opacity: 1,
    y: 0,
    transition: {
      delay: i * 0.15,
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
      custom={custom}
    >
      <div className="flex items-center mb-5">
        <motion.div whileHover={{ scale: 1.1 }} className="flex-shrink-0">
           <Icon className="w-8 h-8 text-blue-600 mr-4" />
        </motion.div>
        <h2 className="text-2xl font-semibold text-gray-900">{title}</h2>
      </div>
      <div className="text-gray-700 leading-relaxed space-y-3">
        {children}
      </div>
    </motion.div>
  );
};

const ReturnsPolicyPage: React.FC = () => {
  const sections = [
    {
      icon: FiCalendar,
      title: "Our Return Window",
      content: (
        <>
          <p>We want you to love your shoes! If you're not completely satisfied, you can return most unworn items within <strong>30 days</strong> of the delivery date for a full refund or exchange.</p>
          <p>Items must be returned in their original, unworn condition, including the original shoebox and any accessories.</p>
        </>
      )
    },
    {
      icon: FiCheckSquare,
      title: "Eligibility & Conditions",
      content: (
        <>
          <p>To be eligible for a return:</p>
          <ul className="list-disc list-inside space-y-1 pl-4">
            <li>Item(s) must be unworn, undamaged, and in resalable condition.</li>
            <li>Item(s) must be in the original shoebox (please don't use the shoebox as the shipping box).</li>
            <li>Return must be initiated within 30 days of delivery.</li>
            <li>Proof of purchase (order number, receipt) is required.</li>
          </ul>
          <p className="mt-2">Trying shoes on a clean, carpeted surface is recommended to avoid scuffing the soles.</p>
        </>
      )
    },
    {
      icon: FiList,
      title: "How to Start a Return",
      content: (
        <>
          <p>Starting a return is easy:</p>
          <ol className="list-decimal list-inside space-y-1 pl-4">
            <li>Visit our online <a href="/account/returns" className="text-blue-600 hover:text-blue-800 underline font-medium">Returns Portal</a> (account required) or <Link to="/contact" className="text-blue-600 hover:text-blue-800 underline font-medium">contact our support team</Link> with your order number.</li>
            <li>Follow the instructions to select the item(s) you wish to return and the reason.</li>
            <li>You will receive a prepaid shipping label (for most domestic returns) and packing instructions via email.</li>
            <li>Package your item(s) securely (use a shipping box, not just the shoebox) and drop off at the designated carrier location.</li>
          </ol>
        </>
      )
    },
    {
      icon: FiCreditCard,
      title: "Refunds",
      content: (
        <>
          <p>Once we receive and inspect your return (usually within 3-5 business days of arrival), we will process your refund.</p>
          <p>Refunds will be issued to the original payment method. Please allow an additional 5-10 business days for the refund to reflect in your account, depending on your bank.</p>
          <p>Original shipping charges (if any) are non-refundable.</p>
        </>
      )
    },
    {
      icon: FiRotateCcw,
      title: "Exchanges",
      content: (
        <>
          <p>Need a different size or color? The easiest way to exchange is to return your original item(s) for a refund and place a new order for the desired item(s).</p>
          <p>This ensures you get the item you want quickly, as inventory levels can change. Follow the standard return process above.</p>
        </>
      )
    },
    {
      icon: FiXSquare,
      title: "Exceptions & Non-Returnable Items",
      content: (
        <>
          <p>The following items are generally considered final sale and cannot be returned or exchanged:</p>
          <ul className="list-disc list-inside space-y-1 pl-4">
            <li>Items marked as "Final Sale".</li>
            <li>Gift cards.</li>
            <li>Worn or damaged items (unless due to a manufacturing defect).</li>
            <li>Items returned without the original shoebox or packaging.</li>
          </ul>
          <p className="mt-2">If you believe you received a defective item, please contact customer support immediately.</p>
        </>
      )
    },
    {
      icon: FiHelpCircle,
      title: "Questions?",
      content: (
        <>
          <p>If you have questions about our return policy or a specific return, please contact us.</p>
          <p>Reach out via our <Link to="/contact" className="text-blue-600 hover:text-blue-800 underline font-medium">Contact Page</Link>.</p>
        </>
      )
    }
  ];

  return (
    <div className="bg-gradient-to-b from-blue-50 via-white to-gray-50 min-h-screen py-16 sm:py-24">
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
            Returns & Exchanges
          </motion.h1>
          <motion.p
            className="mt-4 text-lg leading-6 text-gray-600 max-w-3xl mx-auto"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.4, duration: 0.5 }}
          >
            We want you to be completely satisfied with your purchase. Here's how our hassle-free return process works.
          </motion.p>
        </div>

        {/* Policy Sections Grid */}
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

export default ReturnsPolicyPage; 