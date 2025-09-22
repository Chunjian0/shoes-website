import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { FiChevronDown } from 'react-icons/fi';

interface FAQItemProps {
  id: number;
  question: string;
  answer: string;
  isOpen: boolean;
  onClick: () => void;
}

const FAQItem: React.FC<FAQItemProps> = ({ id, question, answer, isOpen, onClick }) => {
  return (
    <motion.div
      initial={false} // Prevent initial animation on load for each item separately
      className="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 bg-white"
    >
      <motion.button
        onClick={onClick}
        className="flex justify-between items-center w-full p-6 text-left text-gray-900 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus-visible:ring focus-visible:ring-indigo-500 focus-visible:ring-opacity-75"
        whileHover={{ backgroundColor: 'rgba(243, 244, 246, 0.7)' }} // Equivalent to hover:bg-gray-50
        aria-expanded={isOpen}
        aria-controls={`faq-answer-${id}`}
      >
        <span className="text-md font-medium">{question}</span>
        <motion.div
          animate={{ rotate: isOpen ? 180 : 0 }}
          transition={{ duration: 0.3 }}
        >
          <FiChevronDown className="w-5 h-5 text-gray-500" />
        </motion.div>
      </motion.button>

      <AnimatePresence initial={false}>
        {isOpen && (
          <motion.div
            id={`faq-answer-${id}`}
            key="content"
            initial="collapsed"
            animate="open"
            exit="collapsed"
            variants={{
              open: { opacity: 1, height: 'auto', marginTop: '0rem', paddingBottom: '1.5rem' },
              collapsed: { opacity: 0, height: 0, marginTop: '0rem', paddingBottom: '0rem' },
            }}
            transition={{ duration: 0.4, ease: [0.04, 0.62, 0.23, 0.98] }}
            className="overflow-hidden"
          >
            {/* Add border-t only when open and content is visible */}
            <div className="px-6 border-t border-gray-200 pt-4">
              <p className="text-gray-700 leading-relaxed">{answer}</p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
};

const FAQPage: React.FC = () => {
  const [openQuestionId, setOpenQuestionId] = useState<number | null>(null);

  const faqData = [
    {
      id: 1,
      question: "How do I place an order?",
      answer: "Browse our products, select the size and color you want, and click 'Add to Cart'. When you're ready, go to your cart and click 'Checkout'. Follow the steps to enter your shipping and payment information. You'll receive an email confirmation once your order is placed."
    },
    {
      id: 2,
      question: "What payment methods do you accept?",
      answer: "We accept major credit cards (Visa, Mastercard, American Express), PayPal, and Apple Pay for a secure and convenient checkout experience."
    },
    {
      id: 3,
      question: "How can I track my order?",
      answer: "Once your order ships, you will receive an email with a tracking number and a link to the carrier's website. You can use this number to track your package's progress."
    },
    {
      id: 4,
      question: "What is your return policy?",
      answer: "We offer a 30-day return policy for unworn items in their original packaging. Please visit our Returns page for detailed instructions and to initiate a return."
    },
    {
      id: 5,
      question: "How do I find the right size?",
      answer: "We recommend checking the size guide available on each product page. It provides specific measurements and international size conversions. If you're between sizes, we generally suggest sizing up for a more comfortable fit."
    },
    {
      id: 6,
      question: "Do you offer international shipping?",
      answer: "Yes, we ship to many countries worldwide! Shipping costs and delivery times vary by destination. Please proceed to checkout to see the available options and costs for your location."
    },
     {
      id: 7,
      question: "How should I care for my shoes?",
      answer: "Care instructions vary depending on the material. Generally, we recommend gentle cleaning with appropriate products. Avoid machine washing unless specified. Check the product description or our Shoe Care guide for specific advice."
    }
  ];

  const handleToggle = (id: number) => {
    setOpenQuestionId(prevId => (prevId === id ? null : id));
  };

  // Stagger animation for the list
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1, // Delay between each item animating in
      },
    },
  };

  const itemVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: { duration: 0.5 },
    },
  };

  return (
    <div className="bg-gradient-to-br from-gray-50 via-white to-blue-50 min-h-screen py-16 sm:py-24">
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
            Frequently Asked Questions
          </motion.h1>
          <motion.p
            className="mt-4 text-lg leading-6 text-gray-600 max-w-2xl mx-auto"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.4, duration: 0.5 }}
          >
            Have questions? We've got answers. If you don't find what you're looking for, feel free to contact us.
          </motion.p>
        </div>

        {/* FAQ Accordion Section */}
        <motion.div
          className="max-w-3xl mx-auto space-y-4"
          variants={containerVariants}
          initial="hidden"
          animate="visible"
        >
          {faqData.map((item) => (
            <motion.div key={item.id} variants={itemVariants}>
              <FAQItem
                id={item.id}
                question={item.question}
                answer={item.answer}
                isOpen={openQuestionId === item.id}
                onClick={() => handleToggle(item.id)}
              />
            </motion.div>
          ))}
        </motion.div>

        {/* Further Assistance Section */}
        <motion.div
          className="mt-20 text-center"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.5 + faqData.length * 0.1, duration: 0.5 }} // Delay based on list animation
        >
          <h2 className="text-xl font-semibold text-gray-900 mb-3">Can't find the answer?</h2>
          <p className="text-gray-600 mb-6">Our support team is happy to help. Reach out to us anytime!</p>
          <a
            href="/contact" // Link to your contact page
            className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:-translate-y-0.5 hover:shadow-lg"
          >
            Contact Support
          </a>
        </motion.div>

      </motion.div>
    </div>
  );
};

export default FAQPage; 