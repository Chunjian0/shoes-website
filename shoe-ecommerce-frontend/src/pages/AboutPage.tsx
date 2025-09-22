import React, { useEffect } from 'react';
import { motion } from 'framer-motion';
import { Helmet } from 'react-helmet';

// Team member type definition
interface TeamMember {
  id: number;
  name: string;
  position: string;
  image: string;
  bio: string;
}

// Timeline event type definition
interface TimelineEvent {
  id: number;
  year: string;
  title: string;
  description: string;
  image?: string;
}

const AboutPage: React.FC = () => {
  // Scroll to top when component mounts
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  // Animation variants for staggered animations
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2
      }
    }
  };

  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: { type: 'spring', stiffness: 100 }
    }
  };

  // Team members data
  const teamMembers: TeamMember[] = [
    {
      id: 1,
      name: 'Sarah Johnson',
      position: 'CEO & Founder',
      image: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
      bio: 'With over 15 years of experience in the footwear industry, Sarah founded YCE Shoes with the mission to transform how people shop for premium shoes.'
    },
    {
      id: 2,
      name: 'Michael Rodriguez',
      position: 'Lead Designer',
      image: 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
      bio: 'Michael combines cutting-edge technology with traditional craftsmanship to create shoe designs that are both innovative and timeless.'
    },
    {
      id: 3,
      name: 'Emma Chang',
      position: 'Marketing Director',
      image: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
      bio: 'Emma ensures our brand message reaches the right audience, creating compelling campaigns that capture the essence of YCE Shoes.'
    },
    {
      id: 4,
      name: 'David Wilson',
      position: 'CTO',
      image: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
      bio: 'David leads our technology initiatives, focusing on creating seamless digital experiences and innovative shopping solutions.'
    }
  ];

  // Company timeline data
  const timeline: TimelineEvent[] = [
    {
      id: 1,
      year: '2015',
      title: 'Foundation',
      description: 'YCE Shoes was founded with a vision to revolutionize the premium footwear market with customizable designs.',
      image: 'https://images.unsplash.com/photo-1574271143515-5cddf8da19be?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 2,
      year: '2018',
      title: 'Expansion',
      description: 'We opened our first flagship store and expanded our online presence to serve customers globally.',
      image: 'https://images.unsplash.com/photo-1604671801908-6f0c6a092c05?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 3,
      year: '2020',
      title: 'Digital Transformation',
      description: 'Launched our revolutionary 3D customization platform, allowing customers to design their perfect pair of shoes online.',
      image: 'https://images.unsplash.com/photo-1610654636218-0965d899da37?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 4,
      year: '2023',
      title: 'Sustainability Initiative',
      description: 'Committed to sustainable manufacturing with eco-friendly materials and carbon-neutral production processes.',
      image: 'https://images.unsplash.com/photo-1593544340816-80fc4f4c7716?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
    }
  ];

  return (
    <>
      <Helmet>
        <title>About Us | YCE Shoes</title>
        <meta name="description" content="Learn about YCE Shoes, our mission, team, and story." />
      </Helmet>

      {/* Hero Section with Parallax */}
      <section className="relative h-[60vh] overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-blue-900/80 to-purple-900/80 z-10"></div>
        <motion.div 
          className="absolute inset-0 bg-cover bg-center"
          style={{ backgroundImage: 'url(https://images.unsplash.com/photo-1575537302964-96cd47c06b1b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80)' }}
          initial={{ scale: 1.1 }}
          animate={{ scale: 1 }}
          transition={{ duration: 0.8 }}
        ></motion.div>
        <div className="container mx-auto relative z-20 h-full flex flex-col justify-center text-white px-4">
          <motion.div
            initial={{ y: 30, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ duration: 0.6 }}
          >
            <h1 className="text-4xl md:text-6xl font-bold mb-4">Our Story</h1>
            <p className="text-xl md:text-2xl max-w-xl">Crafting innovative footwear experiences since 2015</p>
          </motion.div>
        </div>
      </section>

      {/* Mission & Values */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            viewport={{ once: true, margin: "-100px" }}
          >
            <h2 className="text-3xl md:text-4xl font-bold mb-6">Our Mission & Values</h2>
            <div className="w-24 h-1 bg-blue-600 mx-auto mb-8"></div>
            <p className="max-w-3xl mx-auto text-lg text-gray-700">
              At YCE Shoes, we believe that exceptional footwear begins with understanding the unique needs of our customers. 
              We're committed to combining cutting-edge technology with traditional craftsmanship to create shoes that are 
              not just stylish, but also comfortable, durable, and sustainable.
            </p>
          </motion.div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
            <motion.div 
              className="p-8 bg-gray-50 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300"
              whileInView={{ opacity: [0, 1], y: [20, 0] }}
              transition={{ duration: 0.5 }}
              viewport={{ once: true, margin: "-100px" }}
            >
              <div className="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <h3 className="text-xl font-bold mb-4">Innovation</h3>
              <p className="text-gray-700">We continuously push the boundaries of what's possible in footwear design and manufacturing.</p>
            </motion.div>

            <motion.div 
              className="p-8 bg-gray-50 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300"
              whileInView={{ opacity: [0, 1], y: [20, 0] }}
              transition={{ duration: 0.5, delay: 0.2 }}
              viewport={{ once: true, margin: "-100px" }}
            >
              <div className="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <h3 className="text-xl font-bold mb-4">Sustainability</h3>
              <p className="text-gray-700">We're committed to reducing our environmental footprint through responsible production practices.</p>
            </motion.div>

            <motion.div 
              className="p-8 bg-gray-50 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300"
              whileInView={{ opacity: [0, 1], y: [20, 0] }}
              transition={{ duration: 0.5, delay: 0.4 }}
              viewport={{ once: true, margin: "-100px" }}
            >
              <div className="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <h3 className="text-xl font-bold mb-4">Customer Focus</h3>
              <p className="text-gray-700">Every decision we make is driven by enhancing the experience and satisfaction of our customers.</p>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Team Section */}
      <section className="py-20 bg-gray-50">
        <div className="container mx-auto px-4">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            viewport={{ once: true, margin: "-100px" }}
          >
            <h2 className="text-3xl md:text-4xl font-bold mb-6">Meet Our Team</h2>
            <div className="w-24 h-1 bg-blue-600 mx-auto mb-8"></div>
            <p className="max-w-3xl mx-auto text-lg text-gray-700">
              Our diverse team of passionate experts works together to create exceptional footwear and shopping experiences.
            </p>
          </motion.div>

          <motion.div 
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-100px" }}
          >
            {teamMembers.map((member) => (
              <motion.div
                key={member.id}
                className="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300"
                variants={itemVariants}
              >
                <div className="aspect-w-1 aspect-h-1 overflow-hidden">
                  <img 
                    src={member.image} 
                    alt={member.name} 
                    className="w-full h-full object-cover transform hover:scale-105 transition-transform duration-500"
                  />
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-bold mb-1">{member.name}</h3>
                  <p className="text-blue-600 font-medium mb-4">{member.position}</p>
                  <p className="text-gray-700">{member.bio}</p>
                </div>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Company Timeline */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            viewport={{ once: true, margin: "-100px" }}
          >
            <h2 className="text-3xl md:text-4xl font-bold mb-6">Our Journey</h2>
            <div className="w-24 h-1 bg-blue-600 mx-auto mb-8"></div>
            <p className="max-w-3xl mx-auto text-lg text-gray-700">
              From our humble beginnings to where we are today, explore the key milestones that have shaped YCE Shoes.
            </p>
          </motion.div>

          <div className="relative mx-auto max-w-5xl">
            {/* Timeline Line */}
            <div className="absolute left-1/2 transform -translate-x-1/2 w-1 h-full bg-blue-100 z-0"></div>

            {/* Timeline Events */}
            {timeline.map((event, index) => (
              <motion.div
                key={event.id}
                className={`relative z-10 mb-12 flex ${index % 2 === 0 ? 'flex-row-reverse' : 'flex-row'}`}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                viewport={{ once: true, margin: "-100px" }}
              >
                <div className="hidden md:block w-1/2"></div>
                <div className="absolute left-1/2 transform -translate-x-1/2 -translate-y-4 w-8 h-8 bg-blue-600 rounded-full border-4 border-white"></div>
                <div className="md:w-1/2 bg-gray-50 rounded-lg shadow-md p-6 md:mx-4 hover:shadow-lg transition-shadow duration-300">
                  <div className="flex flex-col md:flex-row items-start">
                    {event.image && (
                      <div className="w-full md:w-1/3 mb-4 md:mb-0 md:mr-4">
                        <img src={event.image} alt={event.title} className="w-full h-40 object-cover rounded-md" />
                      </div>
                    )}
                    <div className={`w-full ${event.image ? 'md:w-2/3' : ''}`}>
                      <div className="text-xl md:text-2xl font-bold text-blue-600 mb-2">{event.year}</div>
                      <h3 className="text-lg md:text-xl font-semibold mb-2">{event.title}</h3>
                      <p className="text-gray-700">{event.description}</p>
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Call To Action */}
      <section className="py-20 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div className="container mx-auto px-4 text-center">
          <motion.h2 
            className="text-3xl md:text-4xl font-bold mb-6"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            viewport={{ once: true, margin: "-100px" }}
          >
            Join Us On Our Journey
          </motion.h2>
          <motion.p 
            className="max-w-3xl mx-auto text-lg mb-10"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.1 }}
            viewport={{ once: true, margin: "-100px" }}
          >
            Discover our latest collection and experience the perfect combination of style, comfort, and innovation.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
            viewport={{ once: true, margin: "-100px" }}
          >
            <a 
              href="/products" 
              className="inline-block bg-white text-blue-600 font-bold px-8 py-4 rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
            >
              Explore Our Collection
            </a>
          </motion.div>
        </div>
      </section>
    </>
  );
};

export default AboutPage; 