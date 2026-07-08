#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Anglais 6e — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "anglais_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

quizzes_data = [
  [
    "1",
    "Personnes et personnages",
    "Anglais",
    "6eme",
    [
      {
        "id": "1_1",
        "type": "qcm",
        "question": "What is the name of the main character in the story?",
        "options": [
          "Alice",
          "Bob",
          "Charlie",
          "David"
        ],
        "correct_option": "Alice",
        "explanation": "The main character in the story is named Alice."
      },
      {
        "id": "1_2",
        "type": "vrai-faux",
        "question": "The story takes place in a city.",
        "correct": False,
        "explanation": "The story takes place in a village, not a city."
      },
      {
        "id": "1_3",
        "type": "qcm",
        "question": "Describe the personality of the main character in one sentence.",
        "options": [
          "Curious and adventurous",
          "Shy and timid",
          "Brave and strong",
          "Kind and generous"
        ],
        "correct_option": "Curious and adventurous",
        "explanation": "The main character is known for being curious and adventurous."
      },
      {
        "id": "1_4",
        "type": "qcm",
        "question": "Which of the following characters is a friend of the main character?",
        "options": [
          "Eve",
          "Frank",
          "Grace",
          "Heidi"
        ],
        "correct_option": "Eve",
        "explanation": "Eve is a friend of the main character."
      },
      {
        "id": "1_5",
        "type": "vrai-faux",
        "question": "The main character has a pet dog.",
        "correct": True,
        "explanation": "The main character has a pet dog named Max."
      },
      {
        "id": "1_6",
        "type": "qcm",
        "question": "What is the name of the antagonist in the story?",
        "options": [
          "Mr. Smith",
          "Mr. Johnson",
          "Mr. Brown",
          "Mr. White"
        ],
        "correct_option": "Mr. Smith",
        "explanation": "The antagonist in the story is named Mr. Smith."
      },
      {
        "id": "1_7",
        "type": "qcm",
        "question": "Which character helps the main character solve a problem?",
        "options": [
          "Ivan",
          "Judy",
          "Karl",
          "Leo"
        ],
        "correct_option": "Judy",
        "explanation": "Judy helps the main character solve a problem in the story."
      },
      {
        "id": "1_8",
        "type": "vrai-faux",
        "question": "The main character learns an important lesson by the end of the story.",
        "correct": True,
        "explanation": "By the end of the story, the main character learns an important lesson about friendship."
      }
    ]
  ],
  [
    "2",
    "Le quotidien : vivre, jouer, apprendre",
    "Anglais",
    "6eme",
    [
      {
        "id": "2_1",
        "type": "qcm",
        "question": "What do you usually do after school?",
        "options": [
          "Play video games",
          "Do homework",
          "Go to the park",
          "Watch TV"
        ],
        "correct_option": "Do homework",
        "explanation": "Most students usually do their homework after school."
      },
      {
        "id": "2_2",
        "type": "vrai-faux",
        "question": "I have breakfast in the morning.",
        "correct": True,
        "explanation": "Having breakfast in the morning is a common daily routine."
      },
      {
        "id": "2_3",
        "type": "qcm",
        "question": "Which of the following is a common school subject?",
        "options": [
          "Maths",
          "Cooking",
          "Dancing",
          "Singing"
        ],
        "correct_option": "Maths",
        "explanation": "Maths is a common school subject."
      },
      {
        "id": "2_4",
        "type": "vrai-faux",
        "question": "I brush my teeth twice a day.",
        "correct": True,
        "explanation": "Brushing teeth twice a day is a common daily routine."
      },
      {
        "id": "2_5",
        "type": "qcm",
        "question": "What do you usually do after school? (variante 2)",
        "options": [
          "Play video games",
          "Do homework",
          "Go to the park",
          "Watch TV"
        ],
        "correct_option": "Do homework",
        "explanation": "Most students usually do their homework after school."
      },
      {
        "id": "2_6",
        "type": "vrai-faux",
        "question": "I go to bed at 10 PM.",
        "correct": True,
        "explanation": "Going to bed at 10 PM is a common bedtime for many students."
      },
      {
        "id": "2_7",
        "type": "qcm",
        "question": "Which of the following is a common extracurricular activity?",
        "options": [
          "Soccer",
          "Cooking",
          "Painting",
          "Singing"
        ],
        "correct_option": "Soccer",
        "explanation": "Soccer is a common extracurricular activity for students."
      },
      {
        "id": "2_8",
        "type": "vrai-faux",
        "question": "I have lunch at school.",
        "correct": False,
        "explanation": "Many students have lunch at home, not at school."
      }
    ]
  ],
  [
    "3",
    "Pays et paysages",
    "Anglais",
    "6eme",
    [
      {
        "id": "3_1",
        "type": "qcm",
        "question": "Which of the following is a country?",
        "options": [
          "Paris",
          "France",
          "Europe",
          "London"
        ],
        "correct_option": "France",
        "explanation": "France is a country."
      },
      {
        "id": "3_2",
        "type": "vrai-faux",
        "question": "The Eiffel Tower is in France.",
        "correct": True,
        "explanation": "The Eiffel Tower is located in Paris, France."
      },
      {
        "id": "3_3",
        "type": "qcm",
        "question": "Which of the following is a natural landscape?",
        "options": [
          "Beach",
          "City",
          "Museum",
          "Restaurant"
        ],
        "correct_option": "Beach",
        "explanation": "A beach is a natural landscape, while the others are man-made environments."
      },
      {
        "id": "3_4",
        "type": "vrai-faux",
        "question": "Mount Everest is the tallest mountain in the world.",
        "correct": True,
        "explanation": "Mount Everest is indeed the tallest mountain in the world."
      },
      {
        "id": "3_5",
        "type": "qcm",
        "question": "Which of the following is a city?",
        "options": [
          "Amazon",
          "Sahara",
          "New York",
          "Pacific"
        ],
        "correct_option": "New York",
        "explanation": "New York is a city, while the others are natural features."
      },
      {
        "id": "3_6",
        "type": "vrai-faux",
        "question": "The Sahara Desert is located in Africa.",
        "correct": True,
        "explanation": "The Sahara Desert is indeed located in Africa."
      },
      {
        "id": "3_7",
        "type": "qcm",
        "question": "Which of the following is a famous landmark?",
        "options": [
          "Statue of Liberty",
          "Grand Canyon",
          "Amazon Rainforest",
          "Sahara Desert"
        ],
        "correct_option": "Statue of Liberty",
        "explanation": "The Statue of Liberty is a famous landmark located in New York City."
      },
      {
        "id": "3_8",
        "type": "vrai-faux",
        "question": "The Great Wall of China can be seen from space.",
        "correct": False,
        "explanation": "The Great Wall of China is not visible from space with the naked eye."
      }
    ]
  ],
  [
    "4",
    "arts/divertissement : musique, cinéma, littérature",
    "Anglais",
    "6eme",
    [
      {
        "id": "4_1",
        "type": "qcm",
        "question": "Who is the author of the Harry Potter series?",
        "options": [
          "J.K. Rowling",
          "Stephen King",
          "Roald Dahl",
          "Agatha Christie"
        ],
        "correct_option": "J.K. Rowling",
        "explanation": "J.K. Rowling is the author of the Harry Potter series."
      },
      {
        "id": "4_2",
        "type": "vrai-faux",
        "question": "The movie 'The Lion King' is an animated film.",
        "correct": True,
        "explanation": "'The Lion King' is indeed an animated film produced by Disney."
      },
      {
        "id": "4_3",
        "type": "qcm",
        "question": "Which of the following is a musical instrument?",
        "options": [
          "Guitar",
          "Piano",
          "Drums",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "All of the listed options are musical instruments."
      },
      {
        "id": "4_4",
        "type": "vrai-faux",
        "question": "'To Kill a Mockingbird' is a novel written by Harper Lee.",
        "correct": True,
        "explanation": "'To Kill a Mockingbird' is indeed a novel written by Harper Lee."
      },
      {
        "id": "4_5",
        "type": "qcm",
        "question": "Which of the following movies was directed by Steven Spielberg?",
        "options": [
          "Jurassic Park",
          "E.T. the Extra-Terrestrial",
          "Jaws",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Steven Spielberg directed all of these movies."
      },
      {
        "id": "4_6",
        "type": "vrai-faux",
        "question": "'The Beatles' were a famous rock band from England.",
        "correct": True,
        "explanation": "'The Beatles' were indeed a famous rock band from England."
      },
      {
        "id": "4_7",
        "type": "qcm",
        "question": "Which of the following is a genre of music?",
        "options": [
          "Rock",
          "Jazz",
          "Classical",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "All of the listed options are genres of music."
      },
      {
        "id": "4_8",
        "type": "vrai-faux",
        "question": "'The Lord of the Rings' is a trilogy of books written by J.R.R. Tolkien.",
        "correct": True,
        "explanation": "'The Lord of the Rings' is indeed a trilogy of books written by J.R.R. Tolkien."
      }
    ]
  ],
  [
    "5",
    "environnement/société : écologie, citoyenneté, vie sociale",
    "Anglais",
    "6eme",
    [
      {
        "id": "5_1",
        "type": "qcm",
        "question": "What is the main cause of climate change?",
        "options": [
          "Deforestation",
          "Pollution",
          "Greenhouse gas emissions",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Deforestation, pollution, and greenhouse gas emissions are all major contributors to climate change."
      },
      {
        "id": "5_2",
        "type": "vrai-faux",
        "question": "Recycling helps reduce waste and conserve resources.",
        "correct": True,
        "explanation": "Recycling is an important practice that helps reduce waste and conserve natural resources."
      },
      {
        "id": "5_3",
        "type": "qcm",
        "question": "Which of the following is a renewable energy source?",
        "options": [
          "Solar power",
          "Wind power",
          "Hydropower",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Solar power, wind power, and hydropower are all renewable energy sources."
      },
      {
        "id": "5_4",
        "type": "vrai-faux",
        "question": "Citizens have the right to vote in elections.",
        "correct": True,
        "explanation": "Voting in elections is a fundamental right of citizens in a democratic society."
      },
      {
        "id": "5_5",
        "type": "qcm",
        "question": "Which of the following actions can help protect the environment?",
        "options": [
          "Using public transportation",
          "Planting trees",
          "Reducing plastic use",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Using public transportation, planting trees, and reducing plastic use are all effective ways to help protect the environment."
      },
      {
        "id": "5_6",
        "type": "vrai-faux",
        "question": "'Reduce, Reuse, Recycle' is a common slogan for environmental conservation.",
        "correct": True,
        "explanation": "'Reduce, Reuse, Recycle' is indeed a common slogan that promotes environmental conservation."
      },
      {
        "id": "5_7",
        "type": "qcm",
        "question": "Which of the following is a social issue?",
        "options": [
          "Poverty",
          "Education",
          "Healthcare",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Poverty, education, and healthcare are all important social issues that affect communities around the world."
      },
      {
        "id": "5_8",
        "type": "vrai-faux",
        "question": "Volunteering in your community can help make a positive impact.",
        "correct": True,
        "explanation": "Volunteering is a great way to contribute to your community and make a positive impact on the lives of others."
      }
    ]
  ],
  [
    "6",
    "corps humain/santé : alimentation, sport, bien-être",
    "Anglais",
    "6eme",
    [
      {
        "id": "6_1",
        "type": "qcm",
        "question": "Which of the following is a healthy food choice?",
        "options": [
          "Fruits and vegetables",
          "Candy",
          "Soda",
          "Chips"
        ],
        "correct_option": "Fruits and vegetables",
        "explanation": "Fruits and vegetables are healthy food choices that provide essential nutrients for the body."
      },
      {
        "id": "6_2",
        "type": "vrai-faux",
        "question": "Regular exercise is important for maintaining good health.",
        "correct": True,
        "explanation": "Regular exercise helps improve cardiovascular health, strengthen muscles, and boost overall well-being."
      },
      {
        "id": "6_3",
        "type": "qcm",
        "question": "Which of the following is a benefit of drinking water?",
        "options": [
          "Hydration",
          "Improved digestion",
          "Better skin health",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Drinking water provides hydration, aids in digestion, and promotes better skin health."
      },
      {
        "id": "6_4",
        "type": "vrai-faux",
        "question": "'An apple a day keeps the doctor away' is a common saying about healthy eating.",
        "correct": True,
        "explanation": "This saying emphasizes the importance of eating fruits like apples for maintaining good health."
      },
      {
        "id": "6_5",
        "type": "qcm",
        "question": "Which of the following is a form of physical activity?",
        "options": [
          "Running",
          "Swimming",
          "Cycling",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Running, swimming, and cycling are all forms of physical activity that can help improve fitness and overall health."
      },
      {
        "id": "6_6",
        "type": "vrai-faux",
        "question": "'Sleep is important for good health' is a True statement.",
        "correct": True,
        "explanation": "Getting enough sleep is crucial for maintaining good health, as it allows the body to rest and recover."
      },
      {
        "id": "6_7",
        "type": "qcm",
        "question": "Which of the following is a mental health practice?",
        "options": [
          "Meditation",
          "Journaling",
          "Talking to a friend",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Meditation, journaling, and talking to a friend are all practices that can help improve mental health and well-being."
      },
      {
        "id": "6_8",
        "type": "vrai-faux",
        "question": "'Eating junk food is good for your health' is a False statement.",
        "correct": False,
        "explanation": "Eating junk food regularly can lead to health problems such as obesity, diabetes, and heart disease."
      }
    ]
  ],
  [
    "7",
    "technologie : outils numériques, médias, réseaux sociaux",
    "Anglais",
    "6eme",
    [
      {
        "id": "7_1",
        "type": "qcm",
        "question": "Which of the following is a social media platform?",
        "options": [
          "Facebook",
          "Twitter",
          "Instagram",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Facebook, Twitter, and Instagram are all popular social media platforms."
      },
      {
        "id": "7_2",
        "type": "vrai-faux",
        "question": "Using strong passwords can help protect your online accounts.",
        "correct": True,
        "explanation": "Strong passwords are important for keeping your online accounts secure from unauthorized access."
      },
      {
        "id": "7_3",
        "type": "qcm",
        "question": "Which of the following is a digital tool used for communication?",
        "options": [
          "Email",
          "Text messaging",
          "Video conferencing",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Email, text messaging, and video conferencing are all digital tools that facilitate communication."
      },
      {
        "id": "7_4",
        "type": "vrai-faux",
        "question": "'Cyberbullying is a form of bullying that occurs online' is a True statement.",
        "correct": True,
        "explanation": "Cyberbullying involves using digital platforms to harass, threaten, or humiliate others."
      },
      {
        "id": "7_5",
        "type": "qcm",
        "question": "Which of the following is a benefit of using technology?",
        "options": [
          "Access to information",
          "Improved communication",
          "Entertainment",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Technology provides access to information, improves communication, and offers various forms of entertainment."
      },
      {
        "id": "7_6",
        "type": "vrai-faux",
        "question": "'Spending too much time on screens can have negative effects on health' is a True statement.",
        "correct": True,
        "explanation": "Excessive screen time can lead to issues such as eye strain, sleep disturbances, and reduced physical activity."
      },
      {
        "id": "7_7",
        "type": "qcm",
        "question": "Which of the following is a responsible online behavior?",
        "options": [
          "Sharing personal information",
          "Respecting others' privacy",
          "Engaging in cyberbullying",
          "All of the above"
        ],
        "correct_option": "Respecting others' privacy",
        "explanation": "Respecting others' privacy is an important aspect of responsible online behavior."
      },
      {
        "id": "7_8",
        "type": "vrai-faux",
        "question": "'The internet can be a valuable resource for learning and entertainment' is a True statement.",
        "correct": True,
        "explanation": "The internet offers a wealth of information and entertainment options, making it a valuable resource when used responsibly."
      }
    ]
  ],
  [
    "8",
    "histoire/géographie : événements historiques, lieux, repères temporels",
    "Anglais",
    "6eme",
    [
      {
        "id": "8_1",
        "type": "qcm",
        "question": "Who was the first President of the United States?",
        "options": [
          "George Washington",
          "Thomas Jefferson",
          "Abraham Lincoln",
          "John Adams"
        ],
        "correct_option": "George Washington",
        "explanation": "George Washington was the first President of the United States."
      },
      {
        "id": "8_2",
        "type": "vrai-faux",
        "question": "The Great Wall of China was built to protect against invasions.",
        "correct": True,
        "explanation": "The Great Wall of China was indeed built as a defense against invasions from nomadic tribes."
      },
      {
        "id": "8_3",
        "type": "qcm",
        "question": "Which of the following is a continent?",
        "options": [
          "Asia",
          "Europe",
          "Africa",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Asia, Europe, and Africa are all continents."
      },
      {
        "id": "8_4",
        "type": "vrai-faux",
        "question": "'The Renaissance was a period of cultural and artistic rebirth in Europe' is a True statement.",
        "correct": True,
        "explanation": "The Renaissance was indeed a significant period of cultural and artistic development in Europe."
      },
      {
        "id": "8_5",
        "type": "qcm",
        "question": "Which of the following is a famous historical landmark?",
        "options": [
          "The Colosseum",
          "The Pyramids of Giza",
          "The Taj Mahal",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "The Colosseum, The Pyramids of Giza, and The Taj Mahal are all famous historical landmarks."
      },
      {
        "id": "8_6",
        "type": "vrai-faux",
        "question": "'World War II ended in 1945' is a True statement.",
        "correct": True,
        "explanation": "World War II ended in 1945 with the surrender of Germany and Japan."
      },
      {
        "id": "8_7",
        "type": "qcm",
        "question": "Which of the following is a major river?",
        "options": [
          "Nile",
          "Amazon",
          "Mississippi",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "The Nile, Amazon, and Mississippi are all major rivers in the world."
      },
      {
        "id": "8_8",
        "type": "vrai-faux",
        "question": "'The Industrial Revolution was a period of significant technological and economic change' is a True statement.",
        "correct": True,
        "explanation": "The Industrial Revolution was indeed a period of significant technological and economic change that began in the late 18th century."
      }
    ]
  ],
  [
    "9",
    "sciences : phénomènes naturels, corps humain, espace",
    "Anglais",
    "6eme",
    [
      {
        "id": "9_1",
        "type": "qcm",
        "question": "What is the largest planet in our solar system?",
        "options": [
          "Earth",
          "Mars",
          "Jupiter",
          "Saturn"
        ],
        "correct_option": "Jupiter",
        "explanation": "Jupiter is the largest planet in our solar system."
      },
      {
        "id": "9_2",
        "type": "vrai-faux",
        "question": "The human heart has four chambers.",
        "correct": True,
        "explanation": "The human heart indeed has four chambers: two atria and two ventricles."
      },
      {
        "id": "9_3",
        "type": "qcm",
        "question": "Which of the following is a natural phenomenon?",
        "options": [
          "Earthquake",
          "Tornado",
          "Volcanic eruption",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Earthquakes, tornadoes, and volcanic eruptions are all natural phenomena."
      },
      {
        "id": "9_4",
        "type": "vrai-faux",
        "question": "'The Milky Way is a galaxy that contains our solar system' is a True statement.",
        "correct": True,
        "explanation": "The Milky Way is indeed a galaxy that contains our solar system."
      },
      {
        "id": "9_5",
        "type": "qcm",
        "question": "Which of the following is a state of matter?",
        "options": [
          "Solid",
          "Liquid",
          "Gas",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Solid, liquid, and gas are all states of matter."
      },
      {
        "id": "9_6",
        "type": "vrai-faux",
        "question": "'Photosynthesis is the process by which plants convert sunlight into energy' is a True statement.",
        "correct": True,
        "explanation": "Photosynthesis is indeed the process by which plants convert sunlight into energy."
      },
      {
        "id": "9_7",
        "type": "qcm",
        "question": "Which of the following is a part of the human digestive system?",
        "options": [
          "Stomach",
          "Liver",
          "Intestines",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "The stomach, liver, and intestines are all parts of the human digestive system."
      },
      {
        "id": "9_8",
        "type": "vrai-faux",
        "question": "'The Earth revolves around the Sun' is a True statement.",
        "correct": True,
        "explanation": "The Earth does indeed revolve around the Sun, completing one orbit approximately every 365 days."
      }
    ]
  ],
  [
    "10",
    "langues : vocabulaire, expressions, communication",
    "Anglais",
    "6eme",
    [
      {
        "id": "10_1",
        "type": "qcm",
        "question": "What is the English word for 'chien'?",
        "options": [
          "Cat",
          "Dog",
          "Bird",
          "Fish"
        ],
        "correct_option": "Dog",
        "explanation": "The English word for 'chien' is 'dog'."
      },
      {
        "id": "10_2",
        "type": "vrai-faux",
        "question": "The phrase 'How are you?' is a common greeting in English.",
        "correct": True,
        "explanation": "The phrase 'How are you?' is indeed a common greeting used to ask about someone's well-being in English."
      },
      {
        "id": "10_3",
        "type": "qcm",
        "question": "Which of the following is an expression of gratitude?",
        "options": [
          "Thank you",
          "Please",
          "Excuse me",
          "Sorry"
        ],
        "correct_option": "Thank you",
        "explanation": "The expression 'Thank you' is used to express gratitude in English."
      },
      {
        "id": "10_4",
        "type": "vrai-faux",
        "question": "'Goodbye' is a common way to say farewell in English.",
        "correct": True,
        "explanation": "'Goodbye' is indeed a common way to say farewell in English."
      },
      {
        "id": "10_5",
        "type": "qcm",
        "question": "Which of the following is a synonym for 'happy'?",
        "options": [
          "Sad",
          "Angry",
          "Joyful",
          "Tired"
        ],
        "correct_option": "Joyful",
        "explanation": "The word 'joyful' is a synonym for 'happy', meaning feeling or expressing great pleasure or happiness."
      },
      {
        "id": "10_6",
        "type": "vrai-faux",
        "question": "'Please' is used to make a polite request in English.",
        "correct": True,
        "explanation": "The word 'Please' is indeed used to make polite requests in English."
      },
      {
        "id": "10_7",
        "type": "qcm",
        "question": "Which of the following is an example of a question in English?",
        "options": [
          "What time is it?",
          "- What time is it?",
          "- It is what time?",
          "- Time what is it?"
        ],
        "correct_option": "What time is it?",
        "explanation": "The sentence 'What time is it?' is an example of a question in English, as it is asking for information about the time."
      },
      {
        "id": "10_8",
        "type": "vrai-faux",
        "question": "'Excuse me' is a phrase used to get someone's attention or to apologize in English.",
        "correct": True,
        "explanation": "The phrase 'Excuse me' is indeed used to get someone's attention or to apologize in English."
      }
    ]
  ],
  [
    "11",
    "mathématiques : nombres, opérations, géométrie",
    "Anglais",
    "6eme",
    [
      {
        "id": "11_1",
        "type": "qcm",
        "question": "What is the value of 5 + 3?",
        "options": [
          "6",
          "7",
          "8",
          "9"
        ],
        "correct_option": "8",
        "explanation": "The value of 5 + 3 is 8."
      },
      {
        "id": "11_2",
        "type": "vrai-faux",
        "question": "A triangle has three sides.",
        "correct": True,
        "explanation": "A triangle is a polygon with three edges and three vertices."
      },
      {
        "id": "11_3",
        "type": "qcm",
        "question": "Which of the following is a prime number?",
        "options": [
          "4",
          "6",
          "7",
          "9"
        ],
        "correct_option": "7",
        "explanation": "The number 7 is a prime number because it has only two distinct positive divisors: 1 and itself."
      },
      {
        "id": "11_4",
        "type": "vrai-faux",
        "question": "'The area of a rectangle can be calculated by multiplying its length by its width' is a True statement.",
        "correct": True,
        "explanation": "The area of a rectangle is indeed calculated by multiplying its length by its width."
      },
      {
        "id": "11_5",
        "type": "qcm",
        "question": "What is the value of 12 ÷ 4?",
        "options": [
          "2",
          "3",
          "4",
          "5"
        ],
        "correct_option": "3",
        "explanation": "The value of 12 ÷ 4 is 3."
      },
      {
        "id": "11_6",
        "type": "vrai-faux",
        "question": "'A square is a special type of rectangle' is a True statement.",
        "correct": True,
        "explanation": "A square is indeed a special type of rectangle where all sides are equal in length."
      },
      {
        "id": "11_7",
        "type": "qcm",
        "question": "What is the value of 7 - 2?",
        "options": [
          "4",
          "5",
          "6",
          "7"
        ],
        "correct_option": "5",
        "explanation": "The value of 7 - 2 is 5."
      },
      {
        "id": "11_8",
        "type": "vrai-faux",
        "question": "'The sum of the angles in a triangle is 180 degrees' is a True statement.",
        "correct": True,
        "explanation": "The sum of the interior angles of a triangle is indeed 180 degrees."
      }
    ]
  ],
  [
    "12",
    "histoire des arts : œuvres, artistes, mouvements artistiques",
    "Anglais",
    "6eme",
    [
      {
        "id": "12_1",
        "type": "qcm",
        "question": "Who painted the Mona Lisa?",
        "options": [
          "Leonardo da Vinci",
          "Vincent van Gogh",
          "Pablo Picasso",
          "Claude Monet"
        ],
        "correct_option": "Leonardo da Vinci",
        "explanation": "The Mona Lisa was painted by Leonardo da Vinci."
      },
      {
        "id": "12_2",
        "type": "vrai-faux",
        "question": "The Impressionist movement was characterized by a focus on light and color.",
        "correct": True,
        "explanation": "The Impressionist movement indeed emphasized the effects of light and color in art."
      },
      {
        "id": "12_3",
        "type": "qcm",
        "question": "Which of the following is a famous sculpture?",
        "options": [
          "David",
          "The Starry Night",
          "The Persistence of Memory",
          "The Scream"
        ],
        "correct_option": "David",
        "explanation": "'David' is a famous sculpture created by Michelangelo."
      },
      {
        "id": "12_4",
        "type": "vrai-faux",
        "question": "'Pablo Picasso was a leading figure in the Cubist art movement' is a True statement.",
        "correct": True,
        "explanation": "Pablo Picasso was indeed a leading figure in the Cubist art movement, which he co-founded with Georges Braque."
      },
      {
        "id": "12_5",
        "type": "qcm",
        "question": "Which of the following is a famous painting?",
        "options": [
          "The Last Supper",
          "The Thinker",
          "The Kiss",
          "The Birth of Venus"
        ],
        "correct_option": "The Last Supper",
        "explanation": "'The Last Supper' is a famous painting created by Leonardo da Vinci."
      },
      {
        "id": "12_6",
        "type": "vrai-faux",
        "question": "'Claude Monet was known for his landscape paintings' is a True statement.",
        "correct": True,
        "explanation": "Claude Monet was indeed known for his landscape paintings, particularly those depicting gardens and water lilies."
      },
      {
        "id": "12_7",
        "type": "qcm",
        "question": "Which of the following is an art movement?",
        "options": [
          "Surrealism",
          "Realism",
          "Abstract Expressionism",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Surrealism, Realism, and Abstract Expressionism are all recognized art movements in the history of art."
      },
      {
        "id": "12_8",
        "type": "vrai-faux",
        "question": "'The Scream is a famous painting by Edvard Munch' is a True statement.",
        "correct": True,
        "explanation": "'The Scream' is indeed a famous painting created by the Norwegian artist Edvard Munch."
      }
    ]
  ],
  [
    "13",
    "géographie : pays, capitales, continents",
    "Anglais",
    "6eme",
    [
      {
        "id": "13_1",
        "type": "qcm",
        "question": "What is the capital of France?",
        "options": [
          "Berlin",
          "Madrid",
          "Paris",
          "Rome"
        ],
        "correct_option": "Paris",
        "explanation": "The capital of France is Paris."
      },
      {
        "id": "13_2",
        "type": "vrai-faux",
        "question": "The Amazon Rainforest is located in South America.",
        "correct": True,
        "explanation": "The Amazon Rainforest is indeed located in South America, primarily in Brazil."
      },
      {
        "id": "13_3",
        "type": "qcm",
        "question": "Which of the following is a continent?",
        "options": [
          "Asia",
          "Europe",
          "Africa",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Asia, Europe, and Africa are all continents."
      },
      {
        "id": "13_4",
        "type": "vrai-faux",
        "question": "'The Nile is the longest river in the world' is a True statement.",
        "correct": True,
        "explanation": "'The Nile is indeed the longest river in the world, stretching over 6,650 kilometers (4,130 miles)."
      },
      {
        "id": "13_5",
        "type": "qcm",
        "question": "What is the capital of Japan?",
        "options": [
          "Beijing",
          "Seoul",
          "Tokyo",
          "Bangkok"
        ],
        "correct_option": "Tokyo",
        "explanation": "The capital of Japan is Tokyo."
      },
      {
        "id": "13_6",
        "type": "vrai-faux",
        "question": "'Mount Everest is located in the Himalayas' is a True statement.",
        "correct": True,
        "explanation": "'Mount Everest is indeed located in the Himalayas, on the border between Nepal and China."
      },
      {
        "id": "13_7",
        "type": "qcm",
        "question": "Which of the following countries is located in Europe?",
        "options": [
          "Italy",
          "France",
          "Germany",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Italy, France, and Germany are all countries located in Europe, known for their rich history, culture, and landmarks."
      },
      {
        "id": "13_8",
        "type": "vrai-faux",
        "question": "'The Sahara Desert is the largest hot desert in the world' is a True statement.",
        "correct": True,
        "explanation": "'The Sahara Desert is indeed the largest hot desert in the world, covering much of North Africa."
      }
    ]
  ],
  [
    "14",
    "culture générale : cinéma, musique, littérature",
    "Anglais",
    "6eme",
    [
      {
        "id": "14_1",
        "type": "qcm",
        "question": "Who is the author of the Harry Potter series?",
        "options": [
          "J.K. Rowling",
          "Stephen King",
          "Roald Dahl",
          "Rick Riordan"
        ],
        "correct_option": "J.K. Rowling",
        "explanation": "The Harry Potter series was written by J.K. Rowling."
      },
      {
        "id": "14_2",
        "type": "vrai-faux",
        "question": "The movie 'The Lion King' was produced by Disney.",
        "correct": True,
        "explanation": "'The Lion King' is indeed a movie produced by Disney."
      },
      {
        "id": "14_3",
        "type": "qcm",
        "question": "Which of the following is a musical instrument?",
        "options": [
          "Guitar",
          "Piano",
          "Drums",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "All of the listed options are musical instruments."
      },
      {
        "id": "14_4",
        "type": "vrai-faux",
        "question": "'To Kill a Mockingbird' is a novel written by Harper Lee.",
        "correct": True,
        "explanation": "'To Kill a Mockingbird' is indeed a novel written by Harper Lee."
      },
      {
        "id": "14_5",
        "type": "qcm",
        "question": "Which of the following movies was directed by Steven Spielberg?",
        "options": [
          "Jurassic Park",
          "E.T. the Extra-Terrestrial",
          "Jaws",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Steven Spielberg directed all of these movies: 'Jurassic Park', 'E.T. the Extra-Terrestrial', and 'Jaws'."
      },
      {
        "id": "14_6",
        "type": "vrai-faux",
        "question": "'The Beatles' were a famous rock band from England.",
        "correct": True,
        "explanation": "'The Beatles' were indeed a famous rock band from England."
      },
      {
        "id": "14_7",
        "type": "qcm",
        "question": "Which of the following is a genre of music?",
        "options": [
          "Rock",
          "Pop",
          "Jazz",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Rock, Pop, and Jazz are all genres of music, each with its own unique style and characteristics."
      },
      {
        "id": "14_8",
        "type": "vrai-faux",
        "question": "'The Great Gatsby' is a novel written by F. Scott Fitzgerald.",
        "correct": True,
        "explanation": "'The Great Gatsby' is indeed a novel written by F. Scott Fitzgerald."
      }
    ]
  ],
  [
    "15",
    "sports : disciplines, événements, athlètes",
    "Anglais",
    "6eme",
    [
      {
        "id": "15_1",
        "type": "qcm",
        "question": "Which of the following is a popular sport?",
        "options": [
          "Soccer",
          "Basketball",
          "Tennis",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Soccer, basketball, and tennis are all popular sports played around the world."
      },
      {
        "id": "15_2",
        "type": "vrai-faux",
        "question": "The Olympic Games are held every four years.",
        "correct": True,
        "explanation": "The Olympic Games are indeed held every four years, featuring athletes from around the world competing in various sports."
      },
      {
        "id": "15_3",
        "type": "qcm",
        "question": "Who is considered one of the greatest soccer players of all time?",
        "options": [
          "Pelé",
          "Lionel Messi",
          "Cristiano Ronaldo",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Pelé, Lionel Messi, and Cristiano Ronaldo are all considered among the greatest soccer players of all time."
      },
      {
        "id": "15_4",
        "type": "vrai-faux",
        "question": "'The FIFA World Cup is an international soccer tournament' is a True statement.",
        "correct": True,
        "explanation": "The FIFA World Cup is indeed an international soccer tournament held every four years, where national teams compete for the title of world champion."
      },
      {
        "id": "15_5",
        "type": "qcm",
        "question": "Which of the following is a type of tennis court surface?",
        "options": [
          "Clay",
          "Grass",
          "Hard",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Clay is one of the types of tennis court surfaces, along with grass and hard courts. Each surface has its own characteristics that affect how the ball bounces and how players perform."
      },
      {
        "id": "15_6",
        "type": "vrai-faux",
        "question": "'Michael Jordan is widely regarded as one of the greatest basketball players of all time' is a True statement.",
        "correct": True,
        "explanation": "Michael Jordan is indeed widely regarded as one of the greatest basketball players of all time."
      },
      {
        "id": "15_7",
        "type": "qcm",
        "question": "Which sport uses a racket and a shuttlecock?",
        "options": [
          "Badminton",
          "Basketball",
          "Football",
          "Swimming"
        ],
        "correct_option": "Badminton",
        "explanation": "Badminton is played with rackets and a shuttlecock."
      },
      {
        "id": "15_8",
        "type": "vrai-faux",
        "question": "Athletes must train regularly to compete in high-level sports.",
        "correct": True,
        "explanation": "Regular training is essential for athletes to perform well and compete at a high level in sports."
      }
    ]
  ],
  [
    "16",
    "musique : genres, instruments, artistes",
    "Anglais",
    "6eme",
    [
      {
        "id": "16_1",
        "type": "qcm",
        "question": "Which of the following is a genre of music?",
        "options": [
          "Rock",
          "Classical",
          "Hip-hop",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Rock, Classical, and Hip-hop are all genres of music, each with its own unique style and characteristics."
      },
      {
        "id": "16_2",
        "type": "vrai-faux",
        "question": "The guitar is a string instrument.",
        "correct": True,
        "explanation": "The guitar is indeed a string instrument, as it produces sound by vibrating strings."
      },
      {
        "id": "16_3",
        "type": "qcm",
        "question": "Who is known as the 'King of Pop'?",
        "options": [
          "Elvis Presley",
          "Michael Jackson",
          "Prince",
          "Madonna"
        ],
        "correct_option": "Michael Jackson",
        "explanation": "Michael Jackson is widely known as the 'King of Pop' due to his significant influence on the music industry and his record-breaking achievements."
      },
      {
        "id": "16_4",
        "type": "vrai-faux",
        "question": "'Beethoven was a famous composer of classical music' is a True statement.",
        "correct": True,
        "explanation": "Ludwig van Beethoven was indeed a famous composer of classical music, known for his symphonies, sonatas, and concertos."
      },
      {
        "id": "16_5",
        "type": "qcm",
        "question": "Which of the following is a wind instrument?",
        "options": [
          "Flute",
          "Saxophone",
          "Trumpet",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "The flute, saxophone, and trumpet are all wind instruments, as they produce sound by the vibration of air."
      },
      {
        "id": "16_6",
        "type": "vrai-faux",
        "question": "'The Beatles were a famous rock band from England' is a True statement.",
        "correct": True,
        "explanation": "The Beatles were indeed a famous rock band from England, known for their influential music and cultural impact."
      },
      {
        "id": "16_7",
        "type": "qcm",
        "question": "Which of the following is a famous music festival?",
        "options": [
          "Coachella",
          "Glastonbury",
          "Lollapalooza",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Coachella, Glastonbury, and Lollapalooza are all famous music festivals that attract large audiences and feature a wide range of musical acts."
      },
      {
        "id": "16_8",
        "type": "vrai-faux",
        "question": "'Madonna is known as the 'Queen of Pop'' is a True statement.",
        "correct": True,
        "explanation": "Madonna is widely known as the 'Queen of Pop' due to her significant influence on the music industry and her record-breaking achievements."
      }
    ]
  ],
  [
    "17",
    "technologie : outils numériques, médias, réseaux sociaux",
    "Anglais",
    "6eme",
    [
      {
        "id": "17_1",
        "type": "qcm",
        "question": "Which of the following is a social media platform?",
        "options": [
          "Facebook",
          "Twitter",
          "Instagram",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Facebook, Twitter, and Instagram are all popular social media platforms."
      },
      {
        "id": "17_2",
        "type": "vrai-faux",
        "question": "Using strong passwords can help protect your online accounts.",
        "correct": True,
        "explanation": "Strong passwords are important for keeping your online accounts secure from unauthorized access."
      },
      {
        "id": "17_3",
        "type": "qcm",
        "question": "Which of the following is a digital tool used for communication?",
        "options": [
          "Email",
          "Text messaging",
          "Video conferencing",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Email, text messaging, and video conferencing are all digital tools that facilitate communication."
      },
      {
        "id": "17_4",
        "type": "vrai-faux",
        "question": "'Cyberbullying is a form of bullying that occurs online' is a True statement.",
        "correct": True,
        "explanation": "Cyberbullying involves using digital platforms to harass, threaten, or humiliate others."
      },
      {
        "id": "17_5",
        "type": "qcm",
        "question": "Which of the following is a benefit of using technology?",
        "options": [
          "Access to information",
          "Improved communication",
          "Entertainment",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Technology provides access to information, improves communication, and offers various forms of entertainment."
      },
      {
        "id": "17_6",
        "type": "vrai-faux",
        "question": "'Spending too much time on screens can have negative effects on health' is a True statement.",
        "correct": True,
        "explanation": "Excessive screen time can lead to issues such as eye strain, sleep disturbances, and reduced physical activity."
      },
      {
        "id": "17_7",
        "type": "qcm",
        "question": "Which of the following is a digital citizenship skill?",
        "options": [
          "Respecting others online",
          "Protecting personal information",
          "Using technology responsibly",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Digital citizenship involves respecting others online, protecting personal information, and using technology responsibly."
      },
      {
        "id": "17_8",
        "type": "vrai-faux",
        "question": "'The internet offers a wealth of information and entertainment options, making it a valuable resource when used responsibly' is a True statement.",
        "correct": True,
        "explanation": "The internet indeed provides a vast array of information and entertainment, but it is important to use it responsibly to avoid potential risks and negative consequences."
      }
    ]
  ],
  [
    "18",
    "histoire : événements, personnages, périodes historiques",
    "Anglais",
    "6eme",
    [
      {
        "id": "18_1",
        "type": "qcm",
        "question": "Who was the first President of the United States?",
        "options": [
          "George Washington",
          "Abraham Lincoln",
          "Thomas Jefferson",
          "John Adams"
        ],
        "correct_option": "George Washington",
        "explanation": "George Washington was the first President of the United States, serving from 1789 to 1797."
      },
      {
        "id": "18_2",
        "type": "vrai-faux",
        "question": "The Great Wall of China was built to protect against invasions.",
        "correct": True,
        "explanation": "The Great Wall of China was indeed built to protect against invasions from nomadic tribes and military incursions."
      },
      {
        "id": "18_3",
        "type": "qcm",
        "question": "Which of the following is a famous historical figure?",
        "options": [
          "Cleopatra",
          "Napoleon Bonaparte",
          "Mahatma Gandhi",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Cleopatra, Napoleon Bonaparte, and Mahatma Gandhi are all famous historical figures known for their significant impact on history."
      },
      {
        "id": "18_4",
        "type": "vrai-faux",
        "question": "'The Renaissance was a period of cultural and artistic rebirth in Europe' is a True statement.",
        "correct": True,
        "explanation": "The Renaissance was indeed a period of cultural and artistic rebirth in Europe, spanning roughly from the 14th to the 17th century."
      },
      {
        "id": "18_5",
        "type": "qcm",
        "question": "Which of the following is a major historical event?",
        "options": [
          "The American Revolution",
          "The French Revolution",
          "World War I",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "The American Revolution, the French Revolution, and World War I are all major historical events that had significant impacts on the course of history."
      },
      {
        "id": "18_6",
        "type": "vrai-faux",
        "question": "'The Cold War was a period of political tension between the United States and the Soviet Union' is a True statement.",
        "correct": True,
        "explanation": "The Cold War was indeed a period of political tension between the United States and the Soviet Union, lasting from the end of World War II until the early 1990s."
      },
      {
        "id": "18_7",
        "type": "qcm",
        "question": "Which of the following is a famous historical landmark?",
        "options": [
          "The Eiffel Tower",
          "The Great Pyramid of Giza",
          "The Statue of Liberty",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "The Eiffel Tower, The Great Pyramid of Giza, and The Statue of Liberty are all famous historical landmarks known for their architectural significance and cultural importance."
      },
      {
        "id": "18_8",
        "type": "vrai-faux",
        "question": "'The Industrial Revolution was a period of significant technological and economic change' is a True statement.",
        "correct": True,
        "explanation": "The Industrial Revolution was indeed a period of significant technological and economic change that began in the late 18th century and transformed societies around the world."
      }
    ]
  ],
  [
    "19",
    "sciences : phénomènes naturels, corps humain, environnement",
    "Anglais",
    "6eme",
    [
      {
        "id": "19_1",
        "type": "qcm",
        "question": "What is the process by which plants make their own food?",
        "options": [
          "Photosynthesis",
          "Respiration",
          "Digestion",
          "Fermentation"
        ],
        "correct_option": "Photosynthesis",
        "explanation": "Photosynthesis is the process by which plants convert sunlight into energy, allowing them to produce their own food."
      },
      {
        "id": "19_2",
        "type": "vrai-faux",
        "question": "The human heart is responsible for pumping blood throughout the body.",
        "correct": True,
        "explanation": "The human heart is indeed responsible for pumping blood throughout the body, delivering oxygen and nutrients to tissues and removing waste products."
      },
      {
        "id": "19_3",
        "type": "qcm",
        "question": "Which of the following is a natural phenomenon?",
        "options": [
          "Earthquake",
          "Tornado",
          "Volcanic eruption",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Earthquakes, tornadoes, and volcanic eruptions are all natural phenomena that occur due to geological and atmospheric processes."
      },
      {
        "id": "19_4",
        "type": "vrai-faux",
        "question": "'Global warming is caused by an increase in greenhouse gases in the atmosphere' is a True statement.",
        "correct": True,
        "explanation": "Global warming is indeed caused by an increase in greenhouse gases, such as carbon dioxide, in the atmosphere, which trap heat and lead to rising global temperatures."
      },
      {
        "id": "19_5",
        "type": "qcm",
        "question": "Which of the following is a part of the human respiratory system?",
        "options": [
          "Lungs",
          "Heart",
          "Liver",
          "Kidneys"
        ],
        "correct_option": "Lungs",
        "explanation": "The lungs are a crucial part of the human respiratory system, responsible for the exchange of oxygen and carbon dioxide."
      },
      {
        "id": "19_6",
        "type": "vrai-faux",
        "question": "'The water cycle includes processes such as evaporation, condensation, and precipitation' is a True statement.",
        "correct": True,
        "explanation": "The water cycle indeed includes processes such as evaporation (water turning into vapor), condensation (vapor cooling and forming clouds), and precipitation (water falling back to the Earth as rain, snow, etc.)."
      },
      {
        "id": "19_7",
        "type": "qcm",
        "question": "Which of the following is a renewable energy source?",
        "options": [
          "Solar power",
          "Wind power",
          "Hydropower",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Solar power, wind power, and hydropower are all renewable energy sources that can be replenished naturally and have a lower environmental impact compared to fossil fuels."
      },
      {
        "id": "19_8",
        "type": "vrai-faux",
        "question": "'Recycling helps reduce waste and conserve natural resources' is a True statement.",
        "correct": True,
        "explanation": "Recycling is indeed an important practice that helps reduce waste, conserve natural resources, and minimize environmental impact by reusing materials instead of discarding them."
      }
    ]
  ],
  [
    "20",
    "langue anglaise : vocabulaire, grammaire, expressions courantes",
    "Anglais",
    "6eme",
    [
      {
        "id": "20_1",
        "type": "qcm",
        "question": "What is the English word for 'chien'?",
        "options": [
          "Cat",
          "Dog",
          "Bird",
          "Fish"
        ],
        "correct_option": "Dog",
        "explanation": "The English word for 'chien' is 'dog'."
      },
      {
        "id": "20_2",
        "type": "vrai-faux",
        "question": "'I am' is the correct way to say 'je suis' in English.",
        "correct": True,
        "explanation": "'I am' is indeed the correct way to say 'je suis' in English."
      },
      {
        "id": "20_3",
        "type": "qcm",
        "question": "Which of the following is a common English greeting?",
        "options": [
          "Hello",
          "Goodbye",
          "Thank you",
          "Please"
        ],
        "correct_option": "Hello",
        "explanation": "'Hello' is a common English greeting used to say hi or welcome someone."
      },
      {
        "id": "20_4",
        "type": "vrai-faux",
        "question": "'The cat is on the table' is a grammatically correct sentence in English.",
        "correct": True,
        "explanation": "'The cat is on the table' is indeed a grammatically correct sentence in English, as it follows the subject-verb-object structure and uses proper prepositions."
      },
      {
        "id": "20_5",
        "type": "qcm",
        "question": "Which of the following words is an adjective?",
        "options": [
          "Happy",
          "Run",
          "Quickly",
          "Table"
        ],
        "correct_option": "Happy",
        "explanation": "'Happy' is an adjective because it describes a noun (a person, place, or thing) by expressing a quality or state of being."
      },
      {
        "id": "20_6",
        "type": "vrai-faux",
        "question": "'Thank you' is a phrase used to express gratitude in English' is a True statement.",
        "correct": True,
        "explanation": "'Thank you' is indeed a phrase used to express gratitude in English."
      },
      {
        "id": "20_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression for saying goodbye?",
        "options": [
          "See you later",
          "Good morning",
          "How are you?",
          "Thank you"
        ],
        "correct_option": "See you later",
        "explanation": "'See you later' is a common English expression used to say goodbye or indicate that you will see someone again in the future."
      },
      {
        "id": "20_8",
        "type": "vrai-faux",
        "question": "'The dog is barking loudly' is a grammatically correct sentence in English' is a True statement.",
        "correct": True,
        "explanation": "'The dog is barking loudly' is indeed a grammatically correct sentence in English, as it follows the subject-verb-adverb structure and uses proper verb tense."
      }
    ]
  ],
  [
    "21",
    "Famille, environnement, actualité simple",
    "Anglais",
    "6ème",
    [
      {
        "id": "21_1",
        "type": "qcm",
        "question": "What is the English word for 'famille'?",
        "options": [
          "Family",
          "Friend",
          "House",
          "School"
        ],
        "correct_option": "Family",
        "explanation": "The English word for 'famille' is 'family'."
      },
      {
        "id": "21_2",
        "type": "vrai-faux",
        "question": "'The environment is important to protect' is a True statement.",
        "correct": True,
        "explanation": "The environment is indeed important to protect, as it provides essential resources and habitats for all living organisms."
      },
      {
        "id": "21_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'environnement'?",
        "options": [
          "Environment",
          "Nature",
          "Earth",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Environment', 'Nature', and 'Earth' are all common English words that can be used to refer to the natural world and the surroundings in which we live."
      },
      {
        "id": "21_4",
        "type": "vrai-faux",
        "question": "'Recycling helps reduce waste and conserve natural resources' is a True statement.",
        "correct": True,
        "explanation": "Recycling is indeed an important practice that helps reduce waste, conserve natural resources, and minimize environmental impact by reusing materials instead of discarding them."
      },
      {
        "id": "21_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'actualité'?",
        "options": [
          "News",
          "Current events",
          "Headlines",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'News', 'Current events', and 'Headlines' are all common English words that can be used to refer to recent or ongoing events and developments in the world."
      },
      {
        "id": "21_6",
        "type": "vrai-faux",
        "question": "'Climate change is a significant global issue' is a True statement.",
        "correct": True,
        "explanation": "'Climate change is indeed a significant global issue that affects ecosystems, weather patterns, and human societies around the world."
      },
      {
        "id": "21_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'simple'?",
        "options": [
          "Simple",
          "Easy",
          "Basic",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Simple', 'Easy', and 'Basic' are all common English words that can be used to describe something that is not complicated or difficult to understand."
      },
      {
        "id": "21_8",
        "type": "vrai-faux",
        "question": "'The internet provides access to a wealth of information and resources' is a True statement.",
        "correct": True,
        "explanation": "The internet indeed provides access to a vast array of information and resources, making it a valuable tool for learning, communication, and entertainment when used responsibly."
      }
    ]
  ],
  [
    "22",
    "Vocabulaire de base : couleurs, nombres, jours de la semaine",
    "Anglais",
    "6ème",
    [
      {
        "id": "22_1",
        "type": "qcm",
        "question": "What is the English word for 'rouge'?",
        "options": [
          "Red",
          "Blue",
          "Green",
          "Yellow"
        ],
        "correct_option": "Red",
        "explanation": "The English word for 'rouge' is 'red'."
      },
      {
        "id": "22_2",
        "type": "vrai-faux",
        "question": "'The number 5 is greater than the number 3' is a True statement.",
        "correct": True,
        "explanation": "The number 5 is indeed greater than the number 3, as it represents a larger quantity."
      },
      {
        "id": "22_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'nombres'?",
        "options": [
          "Numbers",
          "Digits",
          "Numerals",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Numbers', 'Digits', and 'Numerals' are all common English words that can be used to refer to numerical symbols and quantities."
      },
      {
        "id": "22_4",
        "type": "vrai-faux",
        "question": "'The number 10 is less than the number 20' is a True statement.",
        "correct": True,
        "explanation": "The number 10 is indeed less than the number 20, as it represents a smaller quantity."
      },
      {
        "id": "22_5",
        "type": "qcm",
        "question": "What is the English word for 'lundi'?",
        "options": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday"
        ],
        "correct_option": "Monday",
        "explanation": "The English word for 'lundi' is 'Monday'."
      },
      {
        "id": "22_6",
        "type": "vrai-faux",
        "question": "'The week has seven days' is a True statement.",
        "correct": True,
        "explanation": "The week indeed has seven days: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, and Sunday."
      },
      {
        "id": "22_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'couleurs'?",
        "options": [
          "Colors",
          "Hues",
          "Shades",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Colors', 'Hues', and 'Shades' are all common English words that can be used to refer to different aspects of color and visual perception."
      },
      {
        "id": "22_8",
        "type": "vrai-faux",
        "question": "'The color blue is often associated with calmness and tranquility' is a True statement.",
        "correct": True,
        "explanation": "The color blue is indeed often associated with calmness and tranquility, as it can evoke feelings of peace and relaxation."
      }
    ]
  ],
  [
    "23",
    "Expressions courantes : salutations, formules de politesse, phrases simples",
    "Anglais",
    "6ème",
    [
      {
        "id": "23_1",
        "type": "qcm",
        "question": "Which of the following is a common English greeting?",
        "options": [
          "Hello",
          "Goodbye",
          "Thank you",
          "Please"
        ],
        "correct_option": "Hello",
        "explanation": "'Hello' is a common English greeting used to say hi or welcome someone."
      },
      {
        "id": "23_2",
        "type": "vrai-faux",
        "question": "'Thank you' is a phrase used to express gratitude in English' is a True statement.",
        "correct": True,
        "explanation": "'Thank you' is indeed a phrase used to express gratitude in English."
      },
      {
        "id": "23_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression for saying goodbye?",
        "options": [
          "See you later",
          "Good morning",
          "How are you?",
          "Thank you"
        ],
        "correct_option": "See you later",
        "explanation": "'See you later' is a common English expression used to say goodbye or indicate that you will see someone again in the future."
      },
      {
        "id": "23_4",
        "type": "vrai-faux",
        "question": "'Please' is a word used to make requests more polite in English' is a True statement.",
        "correct": True,
        "explanation": "'Please' is indeed a word used to make requests more polite in English."
      },
      {
        "id": "23_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression for asking how someone is doing?",
        "options": [
          "How are you?",
          "What's up?",
          "How's it going?",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'How are you?', 'What's up?', and 'How's it going?' are all common English expressions used to ask how someone is doing or feeling."
      },
      {
        "id": "23_6",
        "type": "vrai-faux",
        "question": "'Excuse me' is a phrase used to get someone's attention or apologize in English' is a True statement.",
        "correct": True,
        "explanation": "'Excuse me' is indeed a phrase used to get someone's attention or apologize in English."
      },
      {
        "id": "23_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression for showing appreciation?",
        "options": [
          "Thank you",
          "You're welcome",
          "Great job",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Thank you', 'You're welcome', and 'Great job' are all common English expressions used to show appreciation."
      },
      {
        "id": "23_8",
        "type": "vrai-faux",
        "question": "'The phrase 'Have a nice day' is a common way to wish someone well in English' is a True statement.",
        "correct": True,
        "explanation": "'Have a nice day' is indeed a common way to wish someone well in English."
      }
    ]
  ],
  [
    "24",
    "Sports : sports populaires, événements sportifs, athlètes célèbres",
    "Anglais",
    "6ème",
    [
      {
        "id": "24_1",
        "type": "qcm",
        "question": "Which of the following is a popular sport?",
        "options": [
          "Soccer",
          "Basketball",
          "Tennis",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Soccer, basketball, and tennis are all popular sports played and watched by millions of people around the world."
      },
      {
        "id": "24_2",
        "type": "vrai-faux",
        "question": "'The Olympic Games are held every four years' is a True statement.",
        "correct": True,
        "explanation": "The Olympic Games are indeed held every four years, bringing together athletes from around the world to compete in various sports."
      },
      {
        "id": "24_3",
        "type": "qcm",
        "question": "Who is known as the 'King of Soccer'?",
        "options": [
          "Pelé",
          "Lionel Messi",
          "Cristiano Ronaldo",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Pelé, Lionel Messi, and Cristiano Ronaldo are all considered among the greatest soccer players of all time, often referred to as the 'King of Soccer' by fans and experts alike."
      },
      {
        "id": "24_4",
        "type": "vrai-faux",
        "question": "'The FIFA World Cup is the most prestigious international soccer tournament' is a True statement.",
        "correct": True,
        "explanation": "The FIFA World Cup is indeed the most prestigious international soccer tournament, held every four years and featuring teams from around the world competing for the title of world champion."
      },
      {
        "id": "24_5",
        "type": "qcm",
        "question": "Which of the following is a famous sporting event?",
        "options": [
          "Super Bowl",
          "Wimbledon",
          "Tour de France",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "The Super Bowl, Wimbledon, and the Tour de France are all famous sporting events that attract large audiences and feature top athletes in their respective sports."
      },
      {
        "id": "24_6",
        "type": "vrai-faux",
        "question": "'Serena Williams is a famous tennis player' is a True statement.",
        "correct": True,
        "explanation": "Serena Williams is indeed a famous tennis player, known for her powerful playing style and numerous Grand Slam titles."
      },
      {
        "id": "24_7",
        "type": "qcm",
        "question": "Which of the following is a popular winter sport?",
        "options": [
          "Skiing",
          "Snowboarding",
          "Ice skating",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Skiing, snowboarding, and ice skating are all popular winter sports enjoyed by people of all ages around the world."
      },
      {
        "id": "24_8",
        "type": "vrai-faux",
        "question": "'Michael Jordan is considered one of the greatest basketball players of all time' is a True statement.",
        "correct": True,
        "explanation": "Michael Jordan is indeed considered one of the greatest basketball players of all time, known for his incredible skill, competitive spirit, and numerous championships with the Chicago Bulls."
      }
    ]
  ],
  [
    "25",
    "Temps : saisons, météo, phénomènes météorologiques",
    "Anglais",
    "6ème",
    [
      {
        "id": "25_1",
        "type": "qcm",
        "question": "Which of the following is a season?",
        "options": [
          "Spring",
          "Summer",
          "Autumn",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Spring, summer, and autumn are all seasons that occur throughout the year, each with its own unique weather patterns and characteristics."
      },
      {
        "id": "25_2",
        "type": "vrai-faux",
        "question": "'The weather can change from day to day' is a True statement.",
        "correct": True,
        "explanation": "The weather can indeed change from day to day due to various atmospheric conditions and factors."
      },
      {
        "id": "25_3",
        "type": "qcm",
        "question": "Which of the following is a common weather phenomenon?",
        "options": [
          "Rain",
          "Snow",
          "Thunderstorms",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Rain, snow, and thunderstorms are all common weather phenomena that can occur in different regions and seasons."
      },
      {
        "id": "25_4",
        "type": "vrai-faux",
        "question": "'The sun is a star that provides light and heat to the Earth' is a True statement.",
        "correct": True,
        "explanation": "The sun is indeed a star that provides light and heat to the Earth, making it essential for life and influencing weather patterns."
      },
      {
        "id": "25_5",
        "type": "qcm",
        "question": "Which of the following is a common weather condition during winter?",
        "options": [
          "Snow",
          "Ice",
          "Cold temperatures",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Snow, ice, and cold temperatures are all common weather conditions that can occur during winter in many regions around the world."
      },
      {
        "id": "25_6",
        "type": "vrai-faux",
        "question": "'A tornado is a violent rotating column of air that extends from a thunderstorm to the ground' is a True statement.",
        "correct": True,
        "explanation": "A tornado is indeed a violent rotating column of air that extends from a thunderstorm to the ground, capable of causing significant damage."
      },
      {
        "id": "25_7",
        "type": "qcm",
        "question": "Which of the following is a common weather phenomenon during summer?",
        "options": [
          "Heatwaves",
          "Thunderstorms",
          "Droughts",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "Heatwaves, thunderstorms, and droughts are all common weather phenomena that can occur during summer in various regions around the world."
      },
      {
        "id": "25_8",
        "type": "vrai-faux",
        "question": "'Climate change can lead to more extreme weather events' is a True statement.",
        "correct": True,
        "explanation": "'Climate change can indeed lead to more extreme weather events, such as stronger storms, heatwaves, and changes in precipitation patterns."
      }
    ]
  ],
  [
    "26",
    "Conjuguaison : verbes réguliers, verbes irréguliers, temps de base",
    "Anglais",
    "6ème",
    [
      {
        "id": "26_1",
        "type": "qcm",
        "question": "Which of the following is a regular verb in English?",
        "options": [
          "Walk",
          "Go",
          "Eat",
          "Have"
        ],
        "correct_option": "Walk",
        "explanation": "A regular verb in English is one that forms its past tense and past participle by adding -ed to the base form. 'Walk' is a regular verb, while 'Go', 'Eat', and 'Have' are irregular verbs."
      },
      {
        "id": "26_2",
        "type": "vrai-faux",
        "question": "'The past tense of 'walk' is 'walked'' is a True statement.",
        "correct": True,
        "explanation": "The past tense of 'walk' is indeed 'walked', following the regular verb conjugation pattern."
      },
      {
        "id": "26_3",
        "type": "qcm",
        "question": "Which of the following is an irregular verb in English?",
        "options": [
          "Run",
          "Jump",
          "Play",
          "Talk"
        ],
        "correct_option": "Run",
        "explanation": "An irregular verb in English is one that does not follow the regular conjugation pattern. 'Run' is an irregular verb, while 'Jump', 'Play', and 'Talk' are regular verbs."
      },
      {
        "id": "26_4",
        "type": "vrai-faux",
        "question": "'The past tense of 'run' is 'ran'' is a True statement.",
        "correct": True,
        "explanation": "The past tense of 'run' is indeed 'ran', which is an irregular conjugation."
      },
      {
        "id": "26_5",
        "type": "qcm",
        "question": "Which of the following is the correct past tense form of 'eat'?",
        "options": [
          "Eated",
          "Eaten",
          "Ate",
          "Eat"
        ],
        "correct_option": "Ate",
        "explanation": "The correct past tense form of 'eat' is 'ate', which is an irregular verb conjugation."
      },
      {
        "id": "26_6",
        "type": "vrai-faux",
        "question": "'The past tense of 'have' is 'had'' is a True statement.",
        "correct": True,
        "explanation": "The past tense of 'have' is indeed 'had', which is an irregular verb conjugation."
      },
      {
        "id": "26_7",
        "type": "qcm",
        "question": "Which of the following is the correct past participle form of 'go'?",
        "options": [
          "Goed",
          "Gone",
          "Went",
          "Go"
        ],
        "correct_option": "Gone",
        "explanation": "The correct past participle form of 'go' is 'gone', which is an irregular verb conjugation."
      },
      {
        "id": "26_8",
        "type": "vrai-faux",
        "question": "'The past participle of 'go' is 'gone'' is a True statement.",
        "correct": True,
        "explanation": "The past participle of 'go' is indeed 'gone', which is used in perfect tenses and passive voice."
      }
    ]
  ],
  [
    "27",
    "Vocabulaire de base : animaux, aliments, objets du quotidien",
    "Anglais",
    "6ème",
    [
      {
        "id": "27_1",
        "type": "qcm",
        "question": "What is the English word for 'chat'?",
        "options": [
          "Cat",
          "Dog",
          "Bird",
          "Fish"
        ],
        "correct_option": "Cat",
        "explanation": "The English word for 'chat' is 'cat'."
      },
      {
        "id": "27_2",
        "type": "vrai-faux",
        "question": "'The English word for 'pomme' is 'apple'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'pomme' is indeed 'apple'."
      },
      {
        "id": "27_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'objets du quotidien'?",
        "options": [
          "Everyday objects",
          "Household items",
          "Daily essentials",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Everyday objects', 'Household items', and 'Daily essentials' are all common English phrases that can be used to refer to objects that are commonly used in daily life."
      },
      {
        "id": "27_4",
        "type": "vrai-faux",
        "question": "'The English word for 'chien' is 'dog'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'chien' is indeed 'dog'."
      },
      {
        "id": "27_5",
        "type": "qcm",
        "question": "What is the English word for 'fromage'?",
        "options": [
          "Cheese",
          "Bread",
          "Milk",
          "Butter"
        ],
        "correct_option": "Cheese",
        "explanation": "The English word for 'fromage' is 'cheese'."
      },
      {
        "id": "27_6",
        "type": "vrai-faux",
        "question": "'The English word for 'maison' is 'house'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'maison' is indeed 'house'."
      },
      {
        "id": "27_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'aliments'?",
        "options": [
          "Food",
          "Groceries",
          "Meals",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Food', 'Groceries', and 'Meals' are all common English words that can be used to refer to items that are consumed for nourishment."
      },
      {
        "id": "27_8",
        "type": "vrai-faux",
        "question": "'The English word for 'voiture' is 'car'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'voiture' is indeed 'car'."
      }
    ]
  ],
  [
    "28",
    "Grammaire de base : articles, prépositions, pronoms personnels",
    "Anglais",
    "6ème",
    [
      {
        "id": "28_1",
        "type": "qcm",
        "question": "Which of the following is a definite article in English?",
        "options": [
          "A",
          "An",
          "The",
          "All of the above"
        ],
        "correct_option": "The",
        "explanation": "The definite article in English is 'the', which is used to refer to specific nouns that are known to the speaker and listener."
      },
      {
        "id": "28_2",
        "type": "vrai-faux",
        "question": "'The indefinite articles in English are 'a' and 'an'' is a True statement.",
        "correct": True,
        "explanation": "The indefinite articles in English are indeed 'a' and 'an', which are used to refer to non-specific nouns."
      },
      {
        "id": "28_3",
        "type": "qcm",
        "question": "Which of the following is a common English preposition?",
        "options": [
          "In",
          "On",
          "At",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In', 'On', and 'At' are all common English prepositions that indicate location, time, or direction."
      },
      {
        "id": "28_4",
        "type": "vrai-faux",
        "question": "'The preposition 'in' is used to indicate location inside something' is a True statement.",
        "correct": True,
        "explanation": "The preposition 'in' is indeed used to indicate location inside something, such as 'The book is in the bag.'"
      },
      {
        "id": "28_5",
        "type": "qcm",
        "question": "Which of the following is a common English personal pronoun?",
        "options": [
          "I",
          "You",
          "He",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'I', 'You', and 'He' are all common English personal pronouns that are used to refer to specific people or things in a sentence."
      },
      {
        "id": "28_6",
        "type": "vrai-faux",
        "question": "'The personal pronoun 'she' is used to refer to a female person' is a True statement.",
        "correct": True,
        "explanation": "The personal pronoun 'she' is indeed used to refer to a female person."
      },
      {
        "id": "28_7",
        "type": "qcm",
        "question": "Which of the following is a common English possessive pronoun?",
        "options": [
          "My",
          "Your",
          "His",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'My', 'Your', and 'His' are all common English possessive pronouns that are used to indicate ownership or possession."
      },
      {
        "id": "28_8",
        "type": "vrai-faux",
        "question": "'The possessive pronoun 'our' is used to refer to something that belongs to us' is a True statement.",
        "correct": True,
        "explanation": "The possessive pronoun 'our' is indeed used to refer to something that belongs to us, such as 'This is our house.'"
      }
    ]
  ],
  [
    "29",
    "Vocabulaire de base : famille, école, loisirs",
    "Anglais",
    "6ème",
    [
      {
        "id": "29_1",
        "type": "qcm",
        "question": "What is the English word for 'père'?",
        "options": [
          "Father",
          "Mother",
          "Brother",
          "Sister"
        ],
        "correct_option": "Father",
        "explanation": "The English word for 'père' is 'father'."
      },
      {
        "id": "29_2",
        "type": "vrai-faux",
        "question": "'The English word for 'mère' is 'mother'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'mère' is indeed 'mother'."
      },
      {
        "id": "29_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'école'?",
        "options": [
          "School",
          "Classroom",
          "Education",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'School', 'Classroom', and 'Education' are all common English words that can be used to refer to the concept of school and learning."
      },
      {
        "id": "29_4",
        "type": "vrai-faux",
        "question": "'The English word for 'loisirs' is 'hobbies'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'loisirs' is indeed 'hobbies', which refers to activities that people enjoy doing in their free time."
      },
      {
        "id": "29_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'frère'?",
        "options": [
          "Brother",
          "Sister",
          "Cousin",
          "Uncle"
        ],
        "correct_option": "Brother",
        "explanation": "The English word for 'frère' is 'brother'."
      },
      {
        "id": "29_6",
        "type": "vrai-faux",
        "question": "'The English word for 'soeur' is 'sister'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'soeur' is indeed 'sister'."
      },
      {
        "id": "29_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'loisirs'?",
        "options": [
          "Hobbies",
          "Leisure activities",
          "Pastimes",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Hobbies', 'Leisure activities', and 'Pastimes' are all common English words that can be used to refer to activities that people enjoy doing in their free time."
      },
      {
        "id": "29_8",
        "type": "vrai-faux",
        "question": "'The English word for 'école' is 'school'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'école' is indeed 'school', which refers to an institution where students receive education and learn various subjects."
      }
    ]
  ],
  [
    "30",
    "Vocabulaire de base : famille, école, loisirs",
    "Anglais",
    "6ème",
    [
      {
        "id": "30_1",
        "type": "qcm",
        "question": "What is the English word for 'père'?",
        "options": [
          "Father",
          "Mother",
          "Brother",
          "Sister"
        ],
        "correct_option": "Father",
        "explanation": "The English word for 'père' is 'father'."
      },
      {
        "id": "30_2",
        "type": "vrai-faux",
        "question": "'The English word for 'mère' is 'mother'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'mère' is indeed 'mother'."
      },
      {
        "id": "30_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'école'?",
        "options": [
          "School",
          "Classroom",
          "Education",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'School', 'Classroom', and 'Education' are all common English words that can be used to refer to the concept of school and learning."
      },
      {
        "id": "30_4",
        "type": "vrai-faux",
        "question": "'The English word for 'loisirs' is 'hobbies'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'loisirs' is indeed 'hobbies', which refers to activities that people enjoy doing in their free time."
      },
      {
        "id": "30_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'frère'?",
        "options": [
          "Brother",
          "Sister",
          "Cousin",
          "Uncle"
        ],
        "correct_option": "Brother",
        "explanation": "The English word for 'frère' is 'brother'."
      },
      {
        "id": "30_6",
        "type": "vrai-faux",
        "question": "'The English word for 'soeur' is 'sister'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'soeur' is indeed 'sister'."
      },
      {
        "id": "30_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'loisirs'?",
        "options": [
          "Hobbies",
          "Leisure activities",
          "Pastimes",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Hobbies', 'Leisure activities', and 'Pastimes' are all common English words that can be used to refer to activities that people enjoy doing in their free time."
      },
      {
        "id": "30_8",
        "type": "vrai-faux",
        "question": "'The English word for 'école' is 'school'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'école' is indeed 'school', which refers to an institution where students receive education and learn various subjects."
      }
    ]
  ],
  [
    "31",
    "Vocabulaire de base : école, loisirs",
    "Anglais",
    "6ème",
    [
      {
        "id": "31_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'école'?",
        "options": [
          "School",
          "Classroom",
          "Education",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'School', 'Classroom', and 'Education' are all common English words that can be used to refer to the concept of school and learning."
      },
      {
        "id": "31_2",
        "type": "vrai-faux",
        "question": "'The English word for 'loisirs' is 'hobbies'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'loisirs' is indeed 'hobbies', which refers to activities that people enjoy doing in their free time."
      },
      {
        "id": "31_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'loisirs'?",
        "options": [
          "Hobbies",
          "Leisure activities",
          "Pastimes",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Hobbies', 'Leisure activities', and 'Pastimes' are all common English words that can be used to refer to activities that people enjoy doing in their free time."
      },
      {
        "id": "31_4",
        "type": "vrai-faux",
        "question": "'The English word for 'école' is 'school'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'école' is indeed 'school', which refers to an institution where students receive education and learn various subjects."
      },
      {
        "id": "31_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'école'? (variante 2)",
        "options": [
          "School",
          "University",
          "College",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'School', 'University', and 'College' are all common English words that can be used to refer to different types of educational institutions."
      },
      {
        "id": "31_6",
        "type": "vrai-faux",
        "question": "'The English word for 'loisirs' is 'leisure activities'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'loisirs' can indeed be translated as 'leisure activities', which refers to activities that people enjoy doing in their free time."
      },
      {
        "id": "31_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'école'? (variante 3)",
        "options": [
          "School",
          "Academy",
          "Institute",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'School', 'Academy', and 'Institute' are all common English words that can be used to refer to different types of educational institutions."
      },
      {
        "id": "31_8",
        "type": "vrai-faux",
        "question": "'The English word for 'loisirs' is 'pastimes'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'loisirs' can indeed be translated as 'pastimes', which refers to activities that people enjoy doing in their free time."
      }
    ]
  ],
  [
    "32",
    "Vocabulaire de base : objets scolaires, objets du quotidien",
    "Anglais",
    "6ème",
    [
      {
        "id": "32_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'stylo'?",
        "options": [
          "Pen",
          "Pencil",
          "Marker",
          "All of the above"
        ],
        "correct_option": "Pen",
        "explanation": "The English word for 'stylo' is 'pen', which is a common writing instrument used for writing or drawing."
      },
      {
        "id": "32_2",
        "type": "vrai-faux",
        "question": "'The English word for 'cahier' is 'notebook'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'cahier' is indeed 'notebook', which is a common item used for writing notes, drawing, or organizing information."
      },
      {
        "id": "32_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression for saying goodbye?",
        "options": [
          "Goodbye",
          "See you later",
          "Take care",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Goodbye', 'See you later', and 'Take care' are all common English expressions used to say goodbye or indicate that you will see someone again in the future."
      },
      {
        "id": "32_4",
        "type": "vrai-faux",
        "question": "'The English word for 'gomme' is 'eraser'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'gomme' is indeed 'eraser', which is a common item used to remove pencil marks from paper."
      },
      {
        "id": "32_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'sac à dos'?",
        "options": [
          "Backpack",
          "Bag",
          "Purse",
          "All of the above"
        ],
        "correct_option": "Backpack",
        "explanation": "The English word for 'sac à dos' is 'backpack', which is a common item used to carry books, supplies, and personal belongings, especially by students."
      },
      {
        "id": "32_6",
        "type": "vrai-faux",
        "question": "'The English word for 'bureau' is 'desk'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'bureau' is indeed 'desk', which is a common piece of furniture used for writing, working, or studying."
      },
      {
        "id": "32_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'règle'?",
        "options": [
          "Ruler",
          "Tape measure",
          "Measuring stick",
          "All of the above"
        ],
        "correct_option": "Ruler",
        "explanation": "The English word for 'règle' is 'ruler', which is a common item used to measure length or draw straight lines."
      },
      {
        "id": "32_8",
        "type": "vrai-faux",
        "question": "'The English word for 'chaise' is 'chair'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'chaise' is indeed 'chair', which is a common piece of furniture used for sitting."
      }
    ]
  ],
  [
    "33",
    "Vocabulaire de base : objets scolaires, objets du quotidien",
    "Anglais",
    "6ème",
    [
      {
        "id": "33_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'stylo'?",
        "options": [
          "Pen",
          "Pencil",
          "Marker",
          "All of the above"
        ],
        "correct_option": "Pen",
        "explanation": "The English word for 'stylo' is 'pen', which is a common writing instrument used for writing or drawing."
      },
      {
        "id": "33_2",
        "type": "vrai-faux",
        "question": "'The English word for 'cahier' is 'notebook'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'cahier' is indeed 'notebook', which is a common item used for writing notes, drawing, or organizing information."
      },
      {
        "id": "33_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression for saying goodbye?",
        "options": [
          "Goodbye",
          "See you later",
          "Take care",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Goodbye', 'See you later', and 'Take care' are all common English expressions used to say goodbye or indicate that you will see someone again in the future."
      },
      {
        "id": "33_4",
        "type": "vrai-faux",
        "question": "'The English word for 'gomme' is 'eraser'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'gomme' is indeed 'eraser', which is a common item used to remove pencil marks from paper."
      },
      {
        "id": "33_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'sac à dos'?",
        "options": [
          "Backpack",
          "Bag",
          "Purse",
          "All of the above"
        ],
        "correct_option": "Backpack",
        "explanation": "The English word for 'sac à dos' is 'backpack', which is a common item used to carry books, supplies, and personal belongings, especially by students."
      },
      {
        "id": "33_6",
        "type": "vrai-faux",
        "question": "'The English word for 'bureau' is 'desk'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'bureau' is indeed 'desk', which is a common piece of furniture used for writing, working, or studying."
      },
      {
        "id": "33_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'règle'?",
        "options": [
          "Ruler",
          "Tape measure",
          "Measuring stick",
          "All of the above"
        ],
        "correct_option": "Ruler",
        "explanation": "The English word for 'règle' is 'ruler', which is a common item used to measure length or draw straight lines."
      },
      {
        "id": "33_8",
        "type": "vrai-faux",
        "question": "'The English word for 'chaise' is 'chair'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'chaise' is indeed 'chair', which is a common piece of furniture used for sitting."
      }
    ]
  ],
  [
    "34",
    "Vocabulaire de base : objets scolaires, objets du quotidien",
    "Anglais",
    "6ème",
    [
      {
        "id": "34_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'stylo'?",
        "options": [
          "Pen",
          "Pencil",
          "Marker",
          "All of the above"
        ],
        "correct_option": "Pen",
        "explanation": "The English word for 'stylo' is 'pen', which is a common writing instrument used for writing or drawing."
      },
      {
        "id": "34_2",
        "type": "vrai-faux",
        "question": "'The English word for 'cahier' is 'notebook'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'cahier' is indeed 'notebook', which is a common item used for writing notes, drawing, or organizing information."
      },
      {
        "id": "34_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression for saying goodbye?",
        "options": [
          "Goodbye",
          "See you later",
          "Take care",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Goodbye', 'See you later', and 'Take care' are all common English expressions used to say goodbye or indicate that you will see someone again in the future."
      },
      {
        "id": "34_4",
        "type": "vrai-faux",
        "question": "'The English word for 'gomme' is 'eraser'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'gomme' is indeed 'eraser', which is a common item used to remove pencil marks from paper."
      },
      {
        "id": "34_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'sac à dos'?",
        "options": [
          "Backpack",
          "Bag",
          "Purse",
          "All of the above"
        ],
        "correct_option": "Backpack",
        "explanation": "The English word for 'sac à dos' is 'backpack', which is a common item used to carry books, supplies, and personal belongings."
      },
      {
        "id": "34_6",
        "type": "vrai-faux",
        "question": "'The English word for 'bureau' is 'desk'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'bureau' is indeed 'desk', which is a common piece of furniture used for writing, working, or studying."
      },
      {
        "id": "34_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'règle'?",
        "options": [
          "Ruler",
          "Tape measure",
          "Measuring stick",
          "All of the above"
        ],
        "correct_option": "Ruler",
        "explanation": "The English word for 'règle' is 'ruler', which is a common item used to measure length or draw straight lines."
      },
      {
        "id": "34_8",
        "type": "vrai-faux",
        "question": "'The English word for 'chaise' is 'chair'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'chaise' is indeed 'chair', which is a common piece of furniture used for sitting."
      }
    ]
  ],
  [
    "35",
    "Vocabulaire de base : vêtements, couleurs, nombres",
    "Anglais",
    "6ème",
    [
      {
        "id": "35_1",
        "type": "qcm",
        "question": "What is the English word for 'chemise'?",
        "options": [
          "Shirt",
          "Pants",
          "Dress",
          "Skirt"
        ],
        "correct_option": "Shirt",
        "explanation": "The English word for 'chemise' is 'shirt', which is a common piece of clothing worn on the upper body."
      },
      {
        "id": "35_2",
        "type": "vrai-faux",
        "question": "'The English word for 'pantalon' is 'pants'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'pantalon' is indeed 'pants', which refers to a common piece of clothing worn on the lower body."
      },
      {
        "id": "35_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'couleurs'?",
        "options": [
          "Colors",
          "Hues",
          "Shades",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Colors', 'Hues', and 'Shades' are all common English words that can be used to refer to different aspects of color and its variations."
      },
      {
        "id": "35_4",
        "type": "vrai-faux",
        "question": "'The English word for 'nombres' is 'numbers'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'nombres' is indeed 'numbers', which refers to mathematical symbols used to represent quantities and perform calculations."
      },
      {
        "id": "35_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'robe'?",
        "options": [
          "Dress",
          "Skirt",
          "Gown",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Dress', 'Skirt', and 'Gown' are all common English words that can be used to refer to different types of clothing worn on the lower body, especially for formal or semi-formal occasions."
      },
      {
        "id": "35_6",
        "type": "vrai-faux",
        "question": "'The English word for 'chapeau' is 'hat'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'chapeau' is indeed 'hat', which is a common accessory worn on the head for protection from the sun, cold, or as a fashion statement."
      },
      {
        "id": "35_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'couleurs'? (variante 2)",
        "options": [
          "Colors",
          "Tints",
          "Tones",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Colors', 'Tints', and 'Tones' are all common English words that can be used to refer to different aspects of color and its variations."
      },
      {
        "id": "35_8",
        "type": "vrai-faux",
        "question": "'The English word for 'nombres' is 'digits'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'nombres' can indeed be translated as 'digits', which refers to the individual symbols used to represent numbers in the decimal system (0-9)."
      }
    ]
  ],
  [
    "36",
    "Vocabulaire de base : couleurs, nombres, formes",
    "Anglais",
    "6ème",
    [
      {
        "id": "36_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'couleurs'?",
        "options": [
          "Colors",
          "Hues",
          "Shades",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Colors', 'Hues', and 'Shades' are all common English words that can be used to refer to different aspects of color and its variations."
      },
      {
        "id": "36_2",
        "type": "vrai-faux",
        "question": "'The English word for 'nombres' is 'numbers'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'nombres' is indeed 'numbers', which refers to mathematical symbols used to represent quantities and perform calculations."
      },
      {
        "id": "36_3",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'formes'?",
        "options": [
          "Shapes",
          "Figures",
          "Geometric forms",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
      },
      {
        "id": "36_4",
        "type": "vrai-faux",
        "question": "'The English word for 'couleurs' is 'colors'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'couleurs' is indeed 'colors', which refers to the visual perception of different wavelengths of light."
      },
      {
        "id": "36_5",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'nombres'?",
        "options": [
          "Numbers",
          "Digits",
          "Numerals",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Numbers', 'Digits', and 'Numerals' are all common English words that can be used to refer to mathematical symbols used to represent quantities and perform calculations."
      },
      {
        "id": "36_6",
        "type": "vrai-faux",
        "question": "'The English word for 'formes' is 'shapes'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
      },
      {
        "id": "36_7",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'couleurs'? (variante 2)",
        "options": [
          "Colors",
          "Tints",
          "Tones",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Colors', 'Tints', and 'Tones' are all common English words that can be used to refer to different aspects of color and its variations."
      },
      {
        "id": "36_8",
        "type": "vrai-faux",
        "question": "'The English word for 'nombres' is 'digits'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'nombres' can indeed be translated as 'digits', which refers to the individual symbols used to represent numbers in the decimal system (0-9)."
      }
    ]
  ],
  [
    "37",
    "Vocabulaire de base : formes et prépositions de lieu",
    "Anglais",
    "6ème",
    [
      {
        "id": "37_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'formes'?",
        "options": [
          "Shapes",
          "Figures",
          "Geometric forms",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
      },
      {
        "id": "37_2",
        "type": "vrai-faux",
        "question": "'The English word for 'formes' is 'shapes'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
      },
      {
        "id": "37_3",
        "type": "qcm",
        "question": "Which of the following is a common English preposition used to indicate location?",
        "options": [
          "In",
          "On",
          "At",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In', 'On', and 'At' are all common English prepositions that can be used to indicate location in different contexts. 'In' is used for enclosed spaces, 'On' is used for surfaces, and 'At' is used for specific points or locations."
      },
      {
        "id": "37_4",
        "type": "vrai-faux",
        "question": "'The preposition 'in' is used to indicate location inside something' is a True statement.",
        "correct": True,
        "explanation": "The preposition 'in' is indeed used to indicate location inside something, such as 'The book is in the bag.'"
      },
      {
        "id": "37_5",
        "type": "qcm",
        "question": "Which of the following is a common English preposition used to indicate location on a surface?",
        "options": [
          "In",
          "On",
          "At",
          "All of the above"
        ],
        "correct_option": "On",
        "explanation": "'On' is a common English preposition used to indicate location on a surface, such as 'The book is on the table.'"
      },
      {
        "id": "37_6",
        "type": "vrai-faux",
        "question": "'The preposition 'at' is used to indicate location at a specific point' is a True statement.",
        "correct": True,
        "explanation": "The preposition 'at' is indeed used to indicate location at a specific point, such as 'I will meet you at the park.'"
      },
      {
        "id": "37_7",
        "type": "qcm",
        "question": "Which of the following is a common English preposition used to indicate location in relation to something else?",
        "options": [
          "In",
          "On",
          "At",
          "Next to"
        ],
        "correct_option": "Next to",
        "explanation": "'Next to' is a common English preposition used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
      },
      {
        "id": "37_8",
        "type": "vrai-faux",
        "question": "'The preposition 'next to' is used to indicate location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "The preposition 'next to' is indeed used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
      }
    ]
  ],
  [
    "38",
    "Vocabulaire de base : formes et prépositions de lieu",
    "Anglais",
    "6ème",
    [
      {
        "id": "38_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'formes'?",
        "options": [
          "Shapes",
          "Figures",
          "Geometric forms",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
      },
      {
        "id": "38_2",
        "type": "vrai-faux",
        "question": "'The English word for 'formes' is 'shapes'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
      },
      {
        "id": "38_3",
        "type": "qcm",
        "question": "Which of the following is a common English preposition used to indicate location?",
        "options": [
          "In",
          "On",
          "At",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In', 'On', and 'At' are all common English prepositions that can be used to indicate location in different contexts. 'In' is used for enclosed spaces, 'On' is used for surfaces, and 'At' is used for specific points or locations."
      },
      {
        "id": "38_4",
        "type": "vrai-faux",
        "question": "'The preposition 'in' is used to indicate location inside something' is a True statement.",
        "correct": True,
        "explanation": "The preposition 'in' is indeed used to indicate location inside something, such as 'The book is in the bag.'"
      },
      {
        "id": "38_5",
        "type": "qcm",
        "question": "Which of the following is a common English preposition used to indicate location on a surface?",
        "options": [
          "In",
          "On",
          "At",
          "All of the above"
        ],
        "correct_option": "On",
        "explanation": "'On' is a common English preposition used to indicate location on a surface, such as 'The book is on the table.'"
      },
      {
        "id": "38_6",
        "type": "vrai-faux",
        "question": "'The preposition 'at' is used to indicate location at a specific point' is a True statement.",
        "correct": True,
        "explanation": "The preposition 'at' is indeed used to indicate location at a specific point, such as 'I will meet you at the park.'"
      },
      {
        "id": "38_7",
        "type": "qcm",
        "question": "Which of the following is a common English preposition used to indicate location in relation to something else?",
        "options": [
          "In",
          "On",
          "At",
          "Next to"
        ],
        "correct_option": "Next to",
        "explanation": "'Next to' is a common English preposition used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
      },
      {
        "id": "38_8",
        "type": "vrai-faux",
        "question": "'The preposition 'next to' is used to indicate location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "The preposition 'next to' is indeed used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
      }
    ]
  ],
  [
    "39",
    "Vocabulaire de base : formes et description de lieu",
    "Anglais",
    "6ème",
    [
      {
        "id": "39_1",
        "type": "qcm",
        "question": "Which of the following is a common English word for 'formes'?",
        "options": [
          "Shapes",
          "Figures",
          "Geometric forms",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
      },
      {
        "id": "39_2",
        "type": "vrai-faux",
        "question": "'The English word for 'formes' is 'shapes'' is a True statement.",
        "correct": True,
        "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
      },
      {
        "id": "39_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "39_4",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      },
      {
        "id": "39_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location behind something?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "Behind",
        "explanation": "'Behind' is a common English expression used to describe location behind something, such as 'The tree is behind the house.'"
      },
      {
        "id": "39_6",
        "type": "vrai-faux",
        "question": "'The expression 'next to' is used to describe location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "'Next to' is indeed an expression used to describe location in relation to something else, such as 'The book is next to the lamp.'"
      },
      {
        "id": "39_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location in relation to something else?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "39_8",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement. (variante 2)",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      }
    ]
  ],
  [
    "40",
    "Vocabulaire de base : description de lieu",
    "Anglais",
    "6ème",
    [
      {
        "id": "40_1",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "40_2",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      },
      {
        "id": "40_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location behind something?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "Behind",
        "explanation": "'Behind' is a common English expression used to describe location behind something, such as 'The tree is behind the house.'"
      },
      {
        "id": "40_4",
        "type": "vrai-faux",
        "question": "'The expression 'next to' is used to describe location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "'Next to' is indeed an expression used to describe location in relation to something else, such as 'The book is next to the lamp.'"
      },
      {
        "id": "40_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location in relation to something else?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "40_6",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement. (variante 2)",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      },
      {
        "id": "40_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location in relation to something else? (variante 2)",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "40_8",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement. (variante 3)",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      }
    ]
  ],
  [
    "41",
    "Vocabulaire de base : Description d'une personne ou d'un lieu",
    "Anglais",
    "6ème",
    [
      {
        "id": "41_1",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location in relation to something else?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "41_2",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      },
      {
        "id": "41_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location behind something?",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "Behind",
        "explanation": "'Behind' is a common English expression used to describe location behind something, such as 'The tree is behind the house.'"
      },
      {
        "id": "41_4",
        "type": "vrai-faux",
        "question": "'The expression 'next to' is used to describe location in relation to something else' is a True statement.",
        "correct": True,
        "explanation": "'Next to' is indeed an expression used to describe location in relation to something else, such as 'The book is next to the lamp.'"
      },
      {
        "id": "41_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location in relation to something else? (variante 2)",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "41_6",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement. (variante 2)",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      },
      {
        "id": "41_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe location in relation to something else? (variante 3)",
        "options": [
          "In front of",
          "Behind",
          "Next to",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
      },
      {
        "id": "41_8",
        "type": "vrai-faux",
        "question": "'The expression 'in front of' is used to describe location in relation to something else' is a True statement. (variante 3)",
        "correct": True,
        "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
      }
    ]
  ],
  [
    "42",
    "Vocabulaire de base : prononciation de chiffres et de lettres",
    "Anglais",
    "6ème",
    [
      {
        "id": "42_1",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the number '1'?",
        "options": [
          "One",
          "Two",
          "Three",
          "Four"
        ],
        "correct_option": "One",
        "explanation": "The correct English pronunciation for the number '1' is 'One'."
      },
      {
        "id": "42_2",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the letter 'A' is 'ay'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the letter 'A' is indeed 'ay', which is a common way to pronounce this letter in English."
      },
      {
        "id": "42_3",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the number '5'?",
        "options": [
          "Five",
          "Six",
          "Seven",
          "Eight"
        ],
        "correct_option": "Five",
        "explanation": "The correct English pronunciation for the number '5' is 'Five'."
      },
      {
        "id": "42_4",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the letter 'B' is 'bee'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the letter 'B' is indeed 'bee', which is a common way to pronounce this letter in English."
      },
      {
        "id": "42_5",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the number '10'?",
        "options": [
          "Ten",
          "Eleven",
          "Twelve",
          "Thirteen"
        ],
        "correct_option": "Ten",
        "explanation": "The correct English pronunciation for the number '10' is 'Ten'."
      },
      {
        "id": "42_6",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the letter 'C' is 'see'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the letter 'C' is indeed 'see', which is a common way to pronounce this letter in English."
      },
      {
        "id": "42_7",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the number '20'?",
        "options": [
          "Twenty",
          "Thirty",
          "Forty",
          "Fifty"
        ],
        "correct_option": "Twenty",
        "explanation": "The correct English pronunciation for the number '20' is 'Twenty'."
      },
      {
        "id": "42_8",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the letter 'D' is 'dee'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the letter 'D' is indeed 'dee', which is a common way to pronounce this letter in English."
      }
    ]
  ],
  [
    "43",
    "Vocabulaire de base : prononciation de mots courants",
    "Anglais",
    "6ème",
    [
      {
        "id": "43_1",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the word 'cat'?",
        "options": [
          "Cat",
          "Cot",
          "Cut",
          "Cup"
        ],
        "correct_option": "Cat",
        "explanation": "The correct English pronunciation for the word 'cat' is 'Cat'."
      },
      {
        "id": "43_2",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the word 'dog' is 'dog'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the word 'dog' is indeed 'dog', which is a common way to pronounce this word in English."
      },
      {
        "id": "43_3",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the word 'house'?",
        "options": [
          "House",
          "Hose",
          "Horse",
          "Home"
        ],
        "correct_option": "House",
        "explanation": "The correct English pronunciation for the word 'house' is 'House'."
      },
      {
        "id": "43_4",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the word 'car' is 'car'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the word 'car' is indeed 'car', which is a common way to pronounce this word in English."
      },
      {
        "id": "43_5",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the word 'tree'?",
        "options": [
          "Tree",
          "Trey",
          "Tea",
          "Try"
        ],
        "correct_option": "Tree",
        "explanation": "The correct English pronunciation for the word 'tree' is 'Tree'."
      },
      {
        "id": "43_6",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the word 'book' is 'book'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the word 'book' is indeed 'book', which is a common way to pronounce this word in English."
      },
      {
        "id": "43_7",
        "type": "qcm",
        "question": "Which of the following is the correct English pronunciation for the word 'water'?",
        "options": [
          "Water",
          "Watter",
          "Woter",
          "Waiter"
        ],
        "correct_option": "Water",
        "explanation": "The correct English pronunciation for the word 'water' is 'Water'."
      },
      {
        "id": "43_8",
        "type": "vrai-faux",
        "question": "'The English pronunciation for the word 'food' is 'food'' is a True statement.",
        "correct": True,
        "explanation": "The English pronunciation for the word 'food' is indeed 'food', which is a common way to pronounce this word in English."
      }
    ]
  ],
  [
    "44",
    "Vocabulaire de base : phrases courantes",
    "Anglais",
    "6ème",
    [
      {
        "id": "44_1",
        "type": "qcm",
        "question": "Which of the following is a common English greeting?",
        "options": [
          "Hello",
          "Hi",
          "Hey",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Hello', 'Hi', and 'Hey' are all common English greetings that can be used in different contexts to greet someone."
      },
      {
        "id": "44_2",
        "type": "vrai-faux",
        "question": "'The English phrase for 'Comment ça va?' is 'How are you?'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'Comment ça va?' is indeed 'How are you?', which is a common way to ask someone about their well-being in English."
      },
      {
        "id": "44_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to say goodbye?",
        "options": [
          "Goodbye",
          "Bye",
          "See you later",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Goodbye', 'Bye', and 'See you later' are all common English expressions used to say goodbye in different contexts."
      },
      {
        "id": "44_4",
        "type": "vrai-faux",
        "question": "'The English phrase for 'Merci' is 'Thank you'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'Merci' is indeed 'Thank you', which is a common way to express gratitude in English."
      },
      {
        "id": "44_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to apologize?",
        "options": [
          "Sorry",
          "I apologize",
          "My apologies",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Sorry', 'I apologize', and 'My apologies' are all common English expressions used to apologize in different contexts."
      },
      {
        "id": "44_6",
        "type": "vrai-faux",
        "question": "The English phrase for 'S'il vous plaît' is 'Please'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'S'il vous plaît' is indeed 'Please', which is a common way to make a polite request in English."
      },
      {
        "id": "44_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to express gratitude?",
        "options": [
          "Thank you",
          "Thanks",
          "I appreciate it",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Thank you', 'Thanks', and 'I appreciate it' are all common English expressions used to express gratitude in different contexts."
      },
      {
        "id": "44_8",
        "type": "vrai-faux",
        "question": "'The English phrase for 'Excusez-moi' is 'Excuse me'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'Excusez-moi' is indeed 'Excuse me', which is a common way to get someone's attention or to apologize for an interruption in English."
      }
    ]
  ],
  [
    "45",
    "Vocabulaire de base : phrases courantes pour dire bonjour, au revoir, merci, s'il vous plaît, etc.",
    "Anglais",
    "6ème",
    [
      {
        "id": "45_1",
        "type": "qcm",
        "question": "Which of the following is a common English greeting?",
        "options": [
          "Hello",
          "Hi",
          "Hey",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Hello', 'Hi', and 'Hey' are all common English greetings that can be used in different contexts to greet someone."
      },
      {
        "id": "45_2",
        "type": "vrai-faux",
        "question": "'The English phrase for 'Comment ça va?' is 'How are you?'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'Comment ça va?' is indeed 'How are you?', which is a common way to ask someone about their well-being in English."
      },
      {
        "id": "45_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to say goodbye?",
        "options": [
          "Goodbye",
          "Bye",
          "See you later",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Goodbye', 'Bye', and 'See you later' are all common English expressions used to say goodbye in different contexts."
      },
      {
        "id": "45_4",
        "type": "vrai-faux",
        "question": "'The English phrase for 'Merci' is 'Thank you'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'Merci' is indeed 'Thank you', which is a common way to express gratitude in English."
      },
      {
        "id": "45_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to apologize?",
        "options": [
          "Sorry",
          "I apologize",
          "My apologies",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Sorry', 'I apologize', and 'My apologies' are all common English expressions used to apologize in different contexts."
      },
      {
        "id": "45_6",
        "type": "vrai-faux",
        "question": "'The English phrase for 'S'il vous plaît' is 'Please'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'S'il vous plaît' is indeed 'Please', which is a common way to make a polite request in English."
      },
      {
        "id": "45_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to express gratitude?",
        "options": [
          "Thank you",
          "Thanks",
          "I appreciate it",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'Thank you', 'Thanks', and 'I appreciate it' are all common English expressions used to express gratitude in different contexts."
      },
      {
        "id": "45_8",
        "type": "vrai-faux",
        "question": "'The English phrase for 'Excusez-moi' is 'Excuse me'' is a True statement.",
        "correct": True,
        "explanation": "The English phrase for 'Excusez-moi' is indeed 'Excuse me', which is a common way to get someone's attention or to apologize for an interruption in English."
      }
    ]
  ],
  [
    "46",
    "Vocabulaire de base : phrases courantes pour parler de l'extérieur",
    "Anglais",
    "6ème",
    [
      {
        "id": "46_1",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the weather outside?",
        "options": [
          "It's sunny",
          "It's raining",
          "It's windy",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'It's sunny', 'It's raining', and 'It's windy' are all common English expressions used to describe the weather outside."
      },
      {
        "id": "46_2",
        "type": "vrai-faux",
        "question": "'The English expression for 'Il fait beau' is 'It's sunny'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'Il fait beau' is indeed 'It's sunny', which is a common way to describe nice weather in English."
      },
      {
        "id": "46_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the weather outside when it's raining?",
        "options": [
          "It's sunny",
          "It's raining",
          "It's windy",
          "All of the above"
        ],
        "correct_option": "It's raining",
        "explanation": "'It's raining' is a common English expression used to describe the weather outside when it's raining."
      },
      {
        "id": "46_4",
        "type": "vrai-faux",
        "question": "The English expression for 'Il pleut' is 'It's raining'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'Il pleut' is indeed 'It's raining', which is a common way to describe rainy weather in English."
      },
      {
        "id": "46_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the weather outside when it's windy?",
        "options": [
          "It's sunny",
          "It's raining",
          "It's windy",
          "All of the above"
        ],
        "correct_option": "It's windy",
        "explanation": "'It's windy' is a common English expression used to describe the weather outside when it's windy."
      },
      {
        "id": "46_6",
        "type": "vrai-faux",
        "question": "The English expression for 'Il fait du vent' is 'It's windy'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'Il fait du vent' is indeed 'It's windy', which is a common way to describe windy weather in English."
      },
      {
        "id": "46_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the weather outside when it's cold?",
        "options": [
          "It's sunny",
          "It's raining",
          "It's cold",
          "All of the above"
        ],
        "correct_option": "It's cold",
        "explanation": "'It's cold' is a common English expression used to describe the weather outside when it's cold."
      },
      {
        "id": "46_8",
        "type": "vrai-faux",
        "question": "The English expression for 'Il fait froid' is 'It's cold'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'Il fait froid' is indeed 'It's cold', which is a common way to describe cold weather in English."
      }
    ]
  ],
  [
    "47",
    "Vocabulaire de base : phrases courantes pour parler de l'intérieur",
    "Anglais",
    "6ème",
    [
      {
        "id": "47_1",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the interior of a house?",
        "options": [
          "It's cozy",
          "It's spacious",
          "It's modern",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'It's cozy', 'It's spacious', and 'It's modern' are all common English expressions used to describe the interior of a house."
      },
      {
        "id": "47_2",
        "type": "vrai-faux",
        "question": "The English expression for 'C'est confortable' is 'It's cozy'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est confortable' is indeed 'It's cozy', which is a common way to describe a comfortable interior in English."
      },
      {
        "id": "47_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the interior of a house when it's spacious?",
        "options": [
          "It's cozy",
          "It's spacious",
          "It's modern",
          "All of the above"
        ],
        "correct_option": "It's spacious",
        "explanation": "'It's spacious' is a common English expression used to describe the interior of a house when it's spacious."
      },
      {
        "id": "47_4",
        "type": "vrai-faux",
        "question": "The English expression for 'C'est spacieux' is 'It's spacious'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est spacieux' is indeed 'It's spacious', which is a common way to describe a spacious interior in English."
      },
      {
        "id": "47_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the interior of a house when it's modern?",
        "options": [
          "It's cozy",
          "It's spacious",
          "It's modern",
          "All of the above"
        ],
        "correct_option": "It's modern",
        "explanation": "'It's modern' is a common English expression used to describe the interior of a house when it's modern."
      },
      {
        "id": "47_6",
        "type": "vrai-faux",
        "question": "The English expression for 'C'est moderne' is 'It's modern'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est moderne' is indeed 'It's modern', which is a common way to describe a modern interior in English."
      },
      {
        "id": "47_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe the interior of a house when it's old-fashioned?",
        "options": [
          "It's cozy",
          "It's spacious",
          "It's old-fashioned",
          "All of the above"
        ],
        "correct_option": "It's old-fashioned",
        "explanation": "'It's old-fashioned' is a common English expression used to describe the interior of a house when it's old-fashioned."
      },
      {
        "id": "47_8",
        "type": "vrai-faux",
        "question": "'The English expression for 'C'est démodé' is 'It's old-fashioned'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est démodé' is indeed 'It's old-fashioned', which is a common way to describe an old-fashioned interior in English."
      }
    ]
  ],
  [
    "48",
    "Vocabulaire de base : phrases courantes pour parler de la voiture",
    "Anglais",
    "6ème",
    [
      {
        "id": "48_1",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe a car?",
        "options": [
          "It's fast",
          "It's slow",
          "It's new",
          "All of the above"
        ],
        "correct_option": "All of the above",
        "explanation": "'It's fast', 'It's slow', and 'It's new' are all common English expressions used to describe a car in different contexts."
      },
      {
        "id": "48_2",
        "type": "vrai-faux",
        "question": "'The English expression for 'C'est rapide' is 'It's fast'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est rapide' is indeed 'It's fast', which is a common way to describe a fast car in English."
      },
      {
        "id": "48_3",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe a car when it's slow?",
        "options": [
          "It's fast",
          "It's slow",
          "It's new",
          "All of the above"
        ],
        "correct_option": "It's slow",
        "explanation": "'It's slow' is a common English expression used to describe a car when it's slow."
      },
      {
        "id": "48_4",
        "type": "vrai-faux",
        "question": "'The English expression for 'C'est lent' is 'It's slow'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est lent' is indeed 'It's slow', which is a common way to describe a slow car in English."
      },
      {
        "id": "48_5",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe a car when it's new?",
        "options": [
          "It's fast",
          "It's slow",
          "It's new",
          "All of the above"
        ],
        "correct_option": "It's new",
        "explanation": "'It's new' is a common English expression used to describe a car when it's new."
      },
      {
        "id": "48_6",
        "type": "vrai-faux",
        "question": "'The English expression for 'C'est neuf' is 'It's new'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est neuf' is indeed 'It's new', which is a common way to describe a new car in English."
      },
      {
        "id": "48_7",
        "type": "qcm",
        "question": "Which of the following is a common English expression used to describe a car when it's old?",
        "options": [
          "It's fast",
          "It's slow",
          "It's old",
          "All of the above"
        ],
        "correct_option": "It's old",
        "explanation": "'It's old' is a common English expression used to describe a car when it's old."
      },
      {
        "id": "48_8",
        "type": "vrai-faux",
        "question": "'The English expression for 'C'est vieux' is 'It's old'' is a True statement.",
        "correct": True,
        "explanation": "The English expression for 'C'est vieux' is indeed 'It's old', which is a common way to describe an old car in English."
      }
    ]
  ]
]
def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    return "vrai-faux"


def build_true_false_statement(question_text, fallback_answer=""):
    question_text = str(question_text).strip()
    fallback_answer = str(fallback_answer).strip().rstrip(".")
    if fallback_answer:
        return f"{question_text} La bonne réponse attendue est : {fallback_answer}."
    return question_text or "Choisis si l'affirmation est vraie ou fausse."


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).isoformat().replace("+00:00", "Z")
    runtime_questions = []
    for question in questions:
        qtype = normalize_question_type(question.get("type", ""))
        if qtype == "qcm":
            runtime_questions.append({"type": "qcm", "question": str(question.get("question", "")), "choices": list(question.get("options", []))})
        elif qtype == "vrai-faux":
            runtime_questions.append({"type": "vrai-faux", "question": str(question.get("question", ""))})
        else:
            raise ValueError(f"Type de question inconnu : {qtype!r}")
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at},
        "quiz": {"title": title, "type": "quiz", "level": level, "subject": subject, "question_count": len(runtime_questions), "passing_score": 70, "time_limit_minutes": 15, "questions": runtime_questions},
        "exercisenotion": [],
        "exerciseresponses": [],
    }

def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        qtype = normalize_question_type(q.get("type", ""))
        if qtype == "qcm":
            answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": q["correct_option"], "correction": q["explanation"]})
        elif qtype == "vrai-faux":
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q["correct"] else "faux", "correction": q["explanation"]})
        else:
            raise ValueError(f"Type de question inconnu : {qtype!r}")
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "level": level, "subject": subject},
        "quiz": {"title": title, "question_count": len(answers), "level": level, "subject": subject, "answers": answers},
    }

def write_json(path, payload):
        with open(path, "w", encoding="utf-8", newline="\n") as f:
                json.dump(payload, f, ensure_ascii=False, indent=2)
                f.write("\n")


def write_quiz_files():
        os.makedirs(QUIZ_DIR, exist_ok=True)
        os.makedirs(ANSWERS_DIR, exist_ok=True)
        os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
        os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)
        for qid, title, subject, level, questions in quizzes_data:
            quiz = make_quiz(qid, title, subject, level, questions)
            answers = make_answers(qid, title, subject, level, questions)
            write_json(os.path.join(QUIZ_DIR, f"{qid}.json"), quiz)
            write_json(os.path.join(ANSWERS_DIR, f"{qid}.json"), answers)
            write_json(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), quiz)
            write_json(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), answers)
        print(f"{len(quizzes_data)} quiz generated in {OUTPUT_DIR} and synced to runtime")

if __name__ == "__main__":
        write_quiz_files()
