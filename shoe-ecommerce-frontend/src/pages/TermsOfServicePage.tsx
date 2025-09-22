import React from 'react';
import { motion } from 'framer-motion';
import { FiFileText, FiUserCheck, FiShoppingCart, FiCode, FiXCircle, FiShield, FiMapPin, FiInfo } from 'react-icons/fi';
import { Link } from 'react-router-dom';

// Animation variants (can reuse from Privacy Policy or define similar ones)
const pageVariants = {
  initial: { opacity: 0, y: 20 },
  animate: { opacity: 1, y: 0, transition: { duration: 0.6, ease: "easeOut" } },
};

const sectionVariants = {
  hidden: { opacity: 0, y: 15 },
  visible: (i: number) => ({
    opacity: 1,
    y: 0,
    transition: {
      delay: i * 0.1 + 0.3,
      duration: 0.5,
      ease: 'easeOut'
    }
  })
};

interface TermSectionProps {
  title: string;
  icon: React.ElementType;
  children: React.ReactNode;
  custom: number; // Index for stagger animation
}

const TermSection: React.FC<TermSectionProps> = ({ title, icon: Icon, children, custom }) => {
  return (
    <motion.div
      className="mb-10"
      variants={sectionVariants}
      initial="hidden"
      animate="visible"
      custom={custom}
    >
      <div className="flex items-center mb-4">
        <Icon className="w-6 h-6 text-green-600 mr-3 flex-shrink-0" />
        <h2 className="text-2xl font-semibold text-gray-800">{title}</h2>
      </div>
      <div className="text-gray-700 leading-relaxed space-y-3 pl-9">
        {children}
      </div>
    </motion.div>
  );
};

const TermsOfServicePage: React.FC = () => {
  const sections = [
    {
      icon: FiInfo,
      title: "Overview",
      content: (
        <>
          <p>This website is operated by [Your Shoe Store Name]. Throughout the site, the terms "we", "us" and "our" refer to [Your Shoe Store Name]. We offer this website, including all information, tools, and services available from this site to you, the user, conditioned upon your acceptance of all terms, conditions, policies, and notices stated here.</p>
          <p>By visiting our site and/or purchasing something from us, you engage in our "Service" and agree to be bound by the following terms and conditions ("Terms of Service", "Terms"), including those additional terms and conditions and policies referenced herein and/or available by hyperlink. Please read these Terms of Service carefully before accessing or using our website.</p>
        </>
      )
    },
    {
      icon: FiUserCheck,
      title: "User Accounts & Responsibilities",
      content: (
        <>
          <p>If you create an account on our website, you are responsible for maintaining the security of your account and you are fully responsible for all activities that occur under the account. You must immediately notify us of any unauthorized uses of your account or any other breaches of security.</p>
          <p>You agree not to use the Service for any illegal or unauthorized purpose nor may you, in the use of the Service, violate any laws in your jurisdiction.</p>
        </>
      )
    },
    {
      icon: FiShoppingCart,
      title: "Products and Services",
      content: (
        <>
          <p>Certain products or services may be available exclusively online through the website. These products or services may have limited quantities and are subject to return or exchange only according to our <Link to="/returns" className="text-blue-600 hover:text-blue-800 underline font-medium">Return Policy</Link>.</p>
          <p>We have made every effort to display as accurately as possible the colors and images of our products. We cannot guarantee that your computer monitor's display of any color will be accurate.</p>
          <p>We reserve the right to limit the sales of our products or Services to any person, geographic region, or jurisdiction. We reserve the right to limit the quantities of any products or services that we offer. All descriptions of products or product pricing are subject to change at any time without notice, at our sole discretion.</p>
        </>
      )
    },
    {
      icon: FiCode,
      title: "Intellectual Property",
      content: (
        <>
          <p>The Service and its original content, features, and functionality are and will remain the exclusive property of [Your Shoe Store Name] and its licensors. The Service is protected by copyright, trademark, and other laws of both [Your Country] and foreign countries.</p>
        </>
      )
    },
    {
      icon: FiXCircle,
      title: "Prohibited Uses",
      content: (
        <>
          <p>In addition to other prohibitions as set forth in the Terms of Service, you are prohibited from using the site or its content: (a) for any unlawful purpose; (b) to solicit others to perform or participate in any unlawful acts; (c) to violate any international, federal, provincial or state regulations, rules, laws, or local ordinances; (d) to infringe upon or violate our intellectual property rights or the intellectual property rights of others; (e) to harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate based on gender, sexual orientation, religion, ethnicity, race, age, national origin, or disability; (f) to submit false or misleading information; (g) to upload or transmit viruses or any other type of malicious code...</p> {/* Truncated for brevity */}
        </>
      )
    },
    {
      icon: FiShield,
      title: "Disclaimer of Warranties; Limitation of Liability",
      content: (
        <>
          <p>We do not guarantee, represent or warrant that your use of our service will be uninterrupted, timely, secure or error-free. We do not warrant that the results that may be obtained from the use of the service will be accurate or reliable.</p>
          <p>In no case shall [Your Shoe Store Name], our directors, officers, employees, affiliates, agents, contractors, interns, suppliers, service providers or licensors be liable for any injury, loss, claim, or any direct, indirect, incidental, punitive, special, or consequential damages of any kind...</p> {/* Truncated for brevity */}
        </>
      )
    },
     {
      icon: FiMapPin,
      title: "Governing Law",
      content: (
        <>
          <p>These Terms of Service and any separate agreements whereby we provide you Services shall be governed by and construed in accordance with the laws of [Your State/Country, e.g., California, USA].</p>
        </>
      )
    },
    {
      icon: FiFileText,
      title: "Changes to Terms of Service",
      content: (
        <>
          <p>You can review the most current version of the Terms of Service at any time on this page. We reserve the right, at our sole discretion, to update, change or replace any part of these Terms of Service by posting updates and changes to our website. It is your responsibility to check our website periodically for changes.</p>
        </>
      )
    },
     {
      icon: FiInfo,
      title: "Contact Information",
      content: (
        <>
          <p>Questions about the Terms of Service should be sent to us at [Your Contact Email] or via our <Link to="/contact" className="text-blue-600 hover:text-blue-800 underline font-medium">Contact Page</Link>.</p>
        </>
      )
    }
  ];

  return (
    <motion.div
      className="bg-gradient-to-b from-green-50 via-white to-gray-100 min-h-screen py-16 sm:py-24"
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
            Terms of Service
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

        {/* Terms Content */}
        <div className="bg-white p-8 sm:p-12 rounded-lg shadow-lg border border-gray-200">
          {sections.map((section, index) => (
            <TermSection
              key={section.title}
              icon={section.icon}
              title={section.title}
              custom={index}
            >
              {section.content}
            </TermSection>
          ))}
        </div>
      </div>
    </motion.div>
  );
};

export default TermsOfServicePage; 