import React from 'react';
import { motion } from 'framer-motion';
import { FiShield, FiUser, FiDatabase, FiGlobe, FiLink, FiMail, FiLock, FiActivity } from 'react-icons/fi';
import { Link } from 'react-router-dom';

// Animation for the main container
const pageVariants = {
  initial: { opacity: 0, y: 20 },
  animate: { opacity: 1, y: 0, transition: { duration: 0.6, ease: "easeOut" } },
};

// Animation for text sections
const sectionVariants = {
  hidden: { opacity: 0, y: 15 },
  visible: (i: number) => ({
    opacity: 1,
    y: 0,
    transition: {
      delay: i * 0.1 + 0.3, // Stagger delay after page load
      duration: 0.5,
      ease: 'easeOut'
    }
  })
};

interface PolicySectionProps {
  title: string;
  icon: React.ElementType;
  children: React.ReactNode;
  custom: number; // Index for stagger animation
}

const PolicySection: React.FC<PolicySectionProps> = ({ title, icon: Icon, children, custom }) => {
  return (
    <motion.div
      className="mb-10"
      variants={sectionVariants}
      initial="hidden"
      animate="visible"
      custom={custom}
    >
      <div className="flex items-center mb-4">
        <Icon className="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" />
        <h2 className="text-2xl font-semibold text-gray-800">{title}</h2>
      </div>
      <div className="text-gray-700 leading-relaxed space-y-3 pl-9">
        {children}
      </div>
    </motion.div>
  );
};

const PrivacyPolicyPage: React.FC = () => {
  const sections = [
    {
      icon: FiShield,
      title: "Introduction",
      content: (
        <>
          <p>Welcome to [Your Shoe Store Name]'s Privacy Policy. We are committed to protecting your personal information and your right to privacy. If you have any questions or concerns about this privacy notice, or our practices with regards to your personal information, please contact us at [Your Contact Email/Link].</p>
          <p>This privacy notice describes how we might use your information if you visit our website at [Your Website URL], purchase products from us, or otherwise engage with us.</p>
        </>
      )
    },
    {
      icon: FiUser,
      title: "Information We Collect",
      content: (
        <>
          <p>We collect personal information that you voluntarily provide to us when you register on the website, express an interest in obtaining information about us or our products, when you participate in activities on the website (such as posting reviews or entering competitions), or otherwise when you contact us.</p>
          <p>The personal information we collect may include the following:</p>
          <ul className="list-disc list-inside space-y-1 pl-4">
            <li><strong>Personal Identification Information:</strong> Name, email address, phone number, shipping address, billing address.</li>
            <li><strong>Payment Data:</strong> We may collect data necessary to process your payment if you make purchases, such as your payment instrument number (e.g., a credit card number), and the security code associated with your payment instrument. All payment data is stored by our payment processor [Mention Payment Processor, e.g., Stripe, PayPal] and you should review its privacy policies.</li>
            <li><strong>Information Automatically Collected:</strong> We automatically collect certain information when you visit, use or navigate the website. This information does not reveal your specific identity but may include device and usage information, such as your IP address, browser and device characteristics, operating system, language preferences, referring URLs, device name, country, location, information about how and when you use our website and other technical information.</li>
          </ul>
        </>
      )
    },
    {
      icon: FiActivity,
      title: "How We Use Your Information",
      content: (
        <>
          <p>We use personal information collected via our website for a variety of business purposes described below:</p>
          <ul className="list-disc list-inside space-y-1 pl-4">
            <li>To facilitate account creation and logon process.</li>
            <li>To manage user accounts.</li>
            <li>To fulfill and manage your orders, payments, returns, and exchanges.</li>
            <li>To send administrative information to you.</li>
            <li>To request feedback and to contact you about your use of our website.</li>
            <li>To send you marketing and promotional communications (if in accordance with your marketing preferences).</li>
            <li>To protect our Services (e.g., for fraud monitoring and prevention).</li>
            <li>To analyze usage trends and improve our website and offerings.</li>
          </ul>
        </>
      )
    },
     {
      icon: FiDatabase,
      title: "Cookies and Tracking Technologies",
      content: (
        <>
          <p>We may use cookies and similar tracking technologies (like web beacons and pixels) to access or store information. Specific information about how we use such technologies and how you can refuse certain cookies is set out in our Cookie Policy [Link to Cookie Policy if separate, otherwise elaborate here].</p>
        </>
      )
    },
    {
      icon: FiLock,
      title: "Data Security",
      content: (
        <>
          <p>We have implemented appropriate technical and organizational security measures designed to protect the security of any personal information we process. However, despite our safeguards and efforts to secure your information, no electronic transmission over the Internet or information storage technology can be guaranteed to be 100% secure.</p>
        </>
      )
    },
    {
      icon: FiGlobe,
      title: "Your Privacy Rights",
      content: (
        <>
          <p>Depending on your location, you may have certain rights regarding your personal information, such as the right to access, correct, update, or request deletion of your personal information. You can usually manage your account settings and communication preferences or contact us directly.</p>
        </>
      )
    },
    {
      icon: FiLink,
      title: "Third-Party Websites",
      content: (
        <>
          <p>The website may contain links to third-party websites that are not affiliated with us. We cannot guarantee the safety and privacy of data you provide to any third parties. We are not responsible for the content or privacy and security practices and policies of any third parties.</p>
        </>
      )
    },
    {
      icon: FiMail,
      title: "Contact Us",
      content: (
        <>
          <p>If you have questions or comments about this notice, you may contact us by email at [Your Contact Email] or by post to:</p>
          <p>[Your Company Name]<br/>[Your Company Address Line 1]<br/>[Your Company Address Line 2]<br/>[City, Postal Code]<br/>[Country]</p>
          <p>Alternatively, use our <Link to="/contact" className="text-blue-600 hover:text-blue-800 underline font-medium">Contact Page</Link>.</p>
        </>
      )
    }
  ];

  return (
    <motion.div
      className="bg-gradient-to-b from-gray-50 via-white to-gray-100 min-h-screen py-16 sm:py-24"
      variants={pageVariants}
      initial="initial"
      animate="animate"
    >
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
        {/* Header Section */}
        <div className="text-center mb-16">
          <motion.h1
            className="text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 mb-4"
            initial={{ scale: 0.9, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ delay: 0.1, duration: 0.5, type: "spring", stiffness: 100 }}
          >
            Privacy Policy
          </motion.h1>
          <motion.p
            className="mt-4 text-lg leading-6 text-gray-600 max-w-3xl mx-auto"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.2, duration: 0.5 }}
          >
            Last Updated: [Date - e.g., October 26, 2023]
          </motion.p>
        </div>

        {/* Policy Content */}
        <div className="bg-white p-8 sm:p-12 rounded-lg shadow-lg border border-gray-200">
          {sections.map((section, index) => (
            <PolicySection
              key={section.title}
              icon={section.icon}
              title={section.title}
              custom={index}
            >
              {section.content}
            </PolicySection>
          ))}

          <motion.div
             className="mt-12 text-center text-sm text-gray-500"
             variants={sectionVariants} // Reuse section variant for final note
             initial="hidden"
             animate="visible"
             custom={sections.length} // Ensure it animates last
          >
             <p>We reserve the right to modify this privacy policy at any time, so please review it frequently. Changes and clarifications will take effect immediately upon their posting on the website.</p>
           </motion.div>
        </div>
      </div>
    </motion.div>
  );
};

export default PrivacyPolicyPage; 