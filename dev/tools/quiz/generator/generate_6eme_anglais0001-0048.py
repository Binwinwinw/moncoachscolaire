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
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "anglais_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

quizzes_data = [
# ─── 0001 – Personnes et personnages	 ───────────────────────────────────
    (
        "0001",
        'Personnes et personnages',
        'Anglais',
        '6eme',
        [
            {
                "id": "0001_1",
                "type": "qcm",
                "question": "What is the name of the main character in the story?",
                "options": ["Alice", "Bob", "Charlie", "David"],
                "correct_option": "Alice",
                "explanation": "The main character in the story is named Alice."
            },
            {
                "id": "0001_2",
                "type": "vrai-faux",
                "question": "The story takes place in a city.",
                "correct": False,
                "explanation": "The story takes place in a village, not a city."
            },
            {
                "id": "0001_3",
                "type": "qcm",
                "question": "Describe the personality of the main character in one sentence.",
                "options": ["Curious and adventurous", "Shy and timid", "Brave and strong", "Kind and generous"],
                "correct_option": "Curious and adventurous",
                "explanation": "The main character is known for being curious and adventurous."
            },
            {
                "id": "0001_4",
                "type": "qcm",
                "question": "Which of the following characters is a friend of the main character?",
                "options": ["Eve", "Frank", "Grace", "Heidi"],
                "correct_option": "Eve",
                "explanation": "Eve is a friend of the main character."
            },
            {
                "id": "0001_5",
                "type": "vrai-faux",
                "question": "The main character has a pet dog.",
                "correct": True,
                "explanation": "The main character has a pet dog named Max."
            },
            {
                "id": "0001_6",
                "type": "qcm",
                "question": "What is the name of the antagonist in the story?",
                "options": ["Mr. Smith", "Mr. Johnson", "Mr. Brown", "Mr. White"],
                "correct_option": "Mr. Smith",
                "explanation": "The antagonist in the story is named Mr. Smith."
            },
            {
                "id": "0001_7",
                "type": "qcm",
                "question": "Which character helps the main character solve a problem?",
                "options": ["Ivan", "Judy", "Karl", "Leo"],
                "correct_option": "Judy",
                "explanation": "Judy helps the main character solve a problem in the story."
            },
            {
                "id": "0001_8",
                "type": "vrai-faux",
                "question": "The main character learns an important lesson by the end of the story.",
                "correct": True,
                "explanation": "By the end of the story, the main character learns an important lesson about friendship."
            },
        ]
    ),
# ─── 0002 – Le quotidien : vivre, jouer, apprendre	 ──────────────────────────
    (
        "0002",
        'Le quotidien : vivre, jouer, apprendre',
        'Anglais',
        '6eme',
        [
            {
                "id": "0002_1",
                "type": "qcm",
                "question": "What do you usually do after school?",
                "options": ["Play video games", "Do homework", "Go to the park", "Watch TV"],
                "correct_option": "Do homework",
                "explanation": "Most students usually do their homework after school."
            },
            {
                "id": "0002_2",
                "type": "vrai-faux",
                "question": "I have breakfast in the morning.",
                "correct": True,
                "explanation": "Having breakfast in the morning is a common daily routine."
            },
            {
                "id": "0002_3",
                "type": "qcm",
                "question": "Which of the following is a common school subject?",
                "options": ["Maths", "Cooking", "Dancing", "Singing"],
                "correct_option": "Maths",
                "explanation": "Maths is a common school subject."
            },
            {
                "id": "0002_4",
                "type": "vrai-faux",
                "question": "I brush my teeth twice a day.",
                "correct": True,
                "explanation": "Brushing teeth twice a day is a common daily routine."
            },
            {
                "id": "0002_5",
                "type": "qcm",
                "question": "What do you usually do after school?",
                "options": ["Play video games", "Do homework", "Go to the park", "Watch TV"],
                "correct_option": "Do homework",
                "explanation": "Most students usually do their homework after school."
            },
            {
                "id": "0002_6",
                "type": "vrai-faux",
                "question": "I go to bed at 10 PM.",
                "correct": True,
                "explanation": "Going to bed at 10 PM is a common bedtime for many students."
            },
            {
                "id": "0002_7",
                "type": "qcm",
                "question": "Which of the following is a common extracurricular activity?",
                "options": ["Soccer", "Cooking", "Painting", "Singing"],
                "correct_option": "Soccer",
                "explanation": "Soccer is a common extracurricular activity for students."
            },
            {
                "id": "0002_8",
                "type": "vrai-faux",
                "question": "I have lunch at school.",
                "correct": False,
                "explanation": "Many students have lunch at home, not at school."
            },
        ]
    ),
# ─── 0003 – Pays et paysages	 ───────────────────────────────────────────────
    (
        "0003",
        'Pays et paysages',
        'Anglais',
        '6eme',
        [
            {
                "id": "0003_1",
                "type": "qcm",
                "question": "Which of the following is a country?",
                "options": ["Paris", "France", "Europe", "London"],
                "correct_option": "France",
                "explanation": "France is a country."
            },
            {
                "id": "0003_2",
                "type": "vrai-faux",
                "question": "The Eiffel Tower is in France.",
                "correct": True,
                "explanation": "The Eiffel Tower is located in Paris, France."
            },
            {
                "id": "0003_3",
                "type": "qcm",
                "question": "Which of the following is a natural landscape?",
                "options": ["Beach", "City", "Museum", "Restaurant"],
                "correct_option": "Beach",
                "explanation": "A beach is a natural landscape, while the others are man-made environments."
            },
            {
                "id": "0003_4",
                "type": "vrai-faux",
                "question": "Mount Everest is the tallest mountain in the world.",
                "correct": True,
                "explanation": "Mount Everest is indeed the tallest mountain in the world."
            },
            {
                "id": "0003_5",
                "type": "qcm",
                "question": "Which of the following is a city?",
                "options": ["Amazon", "Sahara", "New York", "Pacific"],
                "correct_option": "New York",
                "explanation": "New York is a city, while the others are natural features."
            },
            {
                "id": "0003_6",
                "type": "vrai-faux",
                "question": "The Sahara Desert is located in Africa.",
                "correct": True,
                "explanation": "The Sahara Desert is indeed located in Africa."
            },
            {
                "id": "0003_7",
                "type": "qcm",
                "question": "Which of the following is a famous landmark?",
                "options": ["Statue of Liberty", "Grand Canyon", "Amazon Rainforest", "Sahara Desert"],
                "correct_option": "Statue of Liberty",
                "explanation": "The Statue of Liberty is a famous landmark located in New York City."
            },
            {
                "id": "0003_8",
                "type": "vrai-faux",
                "question": "The Great Wall of China can be seen from space.",
                "correct": False,
                "explanation": "The Great Wall of China is not visible from space with the naked eye."
                },
        ]
    ),
# ─── 0004 – Arts et divertissement : musique, cinéma, littérature	 ─────────────
    (
        "0004",
        'arts/divertissement : musique, cinéma, littérature',
        'Anglais',
        '6eme',
        [
            {
                "id": "0004_1",
                "type": "qcm",
                "question": "Who is the author of the Harry Potter series?",
                "options": ["J.K. Rowling", "Stephen King", "Roald Dahl", "Agatha Christie"],
                "correct_option": "J.K. Rowling",
                "explanation": "J.K. Rowling is the author of the Harry Potter series."
            },
            {
                "id": "0004_2",
                "type": "vrai-faux",
                "question": "The movie 'The Lion King' is an animated film.",
                "correct": True,
                "explanation": "'The Lion King' is indeed an animated film produced by Disney."
            },
            {
                "id": "0004_3",
                "type": "qcm",
                "question": "Which of the following is a musical instrument?",
                "options": ["Guitar", "Piano", "Drums", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "All of the listed options are musical instruments."
            },
            {
                "id": "0004_4",
                "type": "vrai-faux",
                "question": "'To Kill a Mockingbird' is a novel written by Harper Lee.",
                "correct": True,
                "explanation": "'To Kill a Mockingbird' is indeed a novel written by Harper Lee."
            },
            {
                "id": "0004_5",
                "type": "qcm",
                "question": "Which of the following movies was directed by Steven Spielberg?",
                "options": ["Jurassic Park", "E.T. the Extra-Terrestrial", "Jaws", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Steven Spielberg directed all of these movies."
            },
            {
                "id": "0004_6",
                "type": "vrai-faux",
                "question": "'The Beatles' were a famous rock band from England.",
                "correct": True,
                "explanation": "'The Beatles' were indeed a famous rock band from England."
            },
            {
                "id": "0004_7",
                "type": "qcm",
                "question": "Which of the following is a genre of music?",
                "options": ["Rock", "Jazz", "Classical", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "All of the listed options are genres of music."
            },
            {
                "id": "0004_8",
                "type": "vrai-faux",
                "question": "'The Lord of the Rings' is a trilogy of books written by J.R.R. Tolkien.",
                "correct": True,
                "explanation": "'The Lord of the Rings' is indeed a trilogy of books written by J.R.R. Tolkien."
            },
        ]
    ),
# ─── 0005 – Environnement/société : écologie, citoyenneté, vie sociale	 ─────────────
    (
        "0005",
        'environnement/société : écologie, citoyenneté, vie sociale',
        'Anglais',
        '6eme',
        [
            {
                "id": "0005_1",
                "type": "qcm",
                "question": "What is the main cause of climate change?",
                "options": ["Deforestation", "Pollution", "Greenhouse gas emissions", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Deforestation, pollution, and greenhouse gas emissions are all major contributors to climate change."
            },
            {
                "id": "0005_2",
                "type": "vrai-faux",
                "question": "Recycling helps reduce waste and conserve resources.",
                "correct": True,
                "explanation": "Recycling is an important practice that helps reduce waste and conserve natural resources."
            },
            {
                "id": "0005_3",
                "type": "qcm",
                "question": "Which of the following is a renewable energy source?",
                "options": ["Solar power", "Wind power", "Hydropower", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Solar power, wind power, and hydropower are all renewable energy sources."
            },
            {
                "id": "0005_4",
                "type": "vrai-faux",
                "question": "Citizens have the right to vote in elections.",
                "correct": True,
                "explanation": "Voting in elections is a fundamental right of citizens in a democratic society."
            },
            {
                "id": "0005_5",
                "type": "qcm",
                "question": "Which of the following actions can help protect the environment?",
                "options": ["Using public transportation", "Planting trees", "Reducing plastic use", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Using public transportation, planting trees, and reducing plastic use are all effective ways to help protect the environment."
            },
            {
                "id": "0005_6",
                "type": "vrai-faux",
                "question": "'Reduce, Reuse, Recycle' is a common slogan for environmental conservation.",
                "correct": True,
                "explanation": "'Reduce, Reuse, Recycle' is indeed a common slogan that promotes environmental conservation."
            },
            {
                "id": "0005_7",
                "type": "qcm",
                "question": "Which of the following is a social issue?",
                "options": ["Poverty", "Education", "Healthcare", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Poverty, education, and healthcare are all important social issues that affect communities around the world."
            },
            {
                "id": "0005_8",
                "type": "vrai-faux",
                "question": "Volunteering in your community can help make a positive impact.",
                "correct": True,
                "explanation": "Volunteering is a great way to contribute to your community and make a positive impact on the lives of others."
            },
        ]
    ),
# ─── 0006 – Corps humain/santé : alimentation, sport, bien-être	 ─────────────────────
    (
        "0006",
        'corps humain/santé : alimentation, sport, bien-être',
        'Anglais',
        '6eme',
        [
            {
                "id": "0006_1",
                "type": "qcm",
                "question": "Which of the following is a healthy food choice?",
                "options": ["Fruits and vegetables", "Candy", "Soda", "Chips"],
                "correct_option": "Fruits and vegetables",
                "explanation": "Fruits and vegetables are healthy food choices that provide essential nutrients for the body."
            },
            {
                "id": "0006_2",
                "type": "vrai-faux",
                "question": "Regular exercise is important for maintaining good health.",
                "correct": True,
                "explanation": "Regular exercise helps improve cardiovascular health, strengthen muscles, and boost overall well-being."
            },
            {
                "id": "0006_3",
                "type": "qcm",
                "question": "Which of the following is a benefit of drinking water?",
                "options": ["Hydration", "Improved digestion", "Better skin health", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Drinking water provides hydration, aids in digestion, and promotes better skin health."
            },
            {
                "id": "0006_4",
                "type": "vrai-faux",
                "question": "'An apple a day keeps the doctor away' is a common saying about healthy eating.",
                "correct": True,
                "explanation": "This saying emphasizes the importance of eating fruits like apples for maintaining good health."
            },
            {
                "id": "0006_5",
                "type": "qcm",
                "question": "Which of the following is a form of physical activity?",
                "options": ["Running", "Swimming", "Cycling", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Running, swimming, and cycling are all forms of physical activity that can help improve fitness and overall health."
            },
            {
                "id": "0006_6",
                "type": "vrai-faux",
                "question": "'Sleep is important for good health' is a true statement.",
                "correct": True,
                "explanation": "Getting enough sleep is crucial for maintaining good health, as it allows the body to rest and recover."
            },
            {
                "id": "0006_7",
                "type": "qcm",
                "question": "Which of the following is a mental health practice?",
                "options": ["Meditation", "Journaling", "Talking to a friend", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Meditation, journaling, and talking to a friend are all practices that can help improve mental health and well-being."
            },
            {
                "id": "0006_8",
                "type": "vrai-faux",
                "question": "'Eating junk food is good for your health' is a false statement.",
                "correct": False,
                "explanation": "Eating junk food regularly can lead to health problems such as obesity, diabetes, and heart disease."
            },
        ]
    ),
# ─── 0007 – Technologie : outils numériques, médias, réseaux sociaux	 ─────────────────────
    (
        "0007",
        'technologie : outils numériques, médias, réseaux sociaux',
        'Anglais',
        '6eme',
        [
            {
                "id": "0007_1",
                "type": "qcm",
                "question": "Which of the following is a social media platform?",
                "options": ["Facebook", "Twitter", "Instagram", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Facebook, Twitter, and Instagram are all popular social media platforms."
            },
            {
                "id": "0007_2",
                "type": "vrai-faux",
                "question": "Using strong passwords can help protect your online accounts.",
                "correct": True,
                "explanation": "Strong passwords are important for keeping your online accounts secure from unauthorized access."
            },
            {
                "id": "0007_3",
                "type": "qcm",
                "question": "Which of the following is a digital tool used for communication?",
                "options": ["Email", "Text messaging", "Video conferencing", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Email, text messaging, and video conferencing are all digital tools that facilitate communication."
            },
            {
                "id": "0007_4",
                "type": "vrai-faux",
                "question": "'Cyberbullying is a form of bullying that occurs online' is a true statement.",
                "correct": True,
                "explanation": "Cyberbullying involves using digital platforms to harass, threaten, or humiliate others."
            },
            {
                "id": "0007_5",
                "type": "qcm",
                "question": "Which of the following is a benefit of using technology?",
                "options": ["Access to information", "Improved communication", "Entertainment", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Technology provides access to information, improves communication, and offers various forms of entertainment."
            },
            {
                "id": "0007_6",
                "type": "vrai-faux",
                "question": "'Spending too much time on screens can have negative effects on health' is a true statement.",
                "correct": True,
                "explanation": "Excessive screen time can lead to issues such as eye strain, sleep disturbances, and reduced physical activity."
            },
            {
                "id": "0007_7",
                "type": "qcm",
                "question": "Which of the following is a responsible online behavior?",
                "options": ["Sharing personal information", "Respecting others' privacy", "Engaging in cyberbullying", "All of the above"],
                "correct_option": "Respecting others' privacy",
                "explanation": "Respecting others' privacy is an important aspect of responsible online behavior."
            },
            {
                "id": "0007_8",
                "type": "vrai-faux",
                "question": "'The internet can be a valuable resource for learning and entertainment' is a true statement.",
                "correct": True,
                "explanation": "The internet offers a wealth of information and entertainment options, making it a valuable resource when used responsibly."
            },
        ]
    ),
# ─── 0008 – Histoire/géographie : événements historiques, lieux, repères temporels	 ─────────────────────
    (
        "0008",
        'histoire/géographie : événements historiques, lieux, repères temporels',
        'Anglais',
        '6eme',
        [
            {
                "id": "0008_1",
                "type": "qcm",
                "question": "Who was the first President of the United States?",
                "options": ["George Washington", "Thomas Jefferson", "Abraham Lincoln", "John Adams"],
                "correct_option": "George Washington",
                "explanation": "George Washington was the first President of the United States."
            },
            {
                "id": "0008_2",
                "type": "vrai-faux",
                "question": "The Great Wall of China was built to protect against invasions.",
                "correct": True,
                "explanation": "The Great Wall of China was indeed built as a defense against invasions from nomadic tribes."
            },
            {
                "id": "0008_3",
                "type": "qcm",
                "question": "Which of the following is a continent?",
                "options": ["Asia", "Europe", "Africa", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Asia, Europe, and Africa are all continents."
            },
            {
                "id": "0008_4",
                "type": "vrai-faux",
                "question": "'The Renaissance was a period of cultural and artistic rebirth in Europe' is a true statement.",
                "correct": True,
                "explanation": "The Renaissance was indeed a significant period of cultural and artistic development in Europe."
            },
            {
                "id": "0008_5",
                "type": "qcm",
                "question": "Which of the following is a famous historical landmark?",
                "options": ["The Colosseum", "The Pyramids of Giza", "The Taj Mahal", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "The Colosseum, The Pyramids of Giza, and The Taj Mahal are all famous historical landmarks."
            },
            {
                "id": "0008_6",
                "type": "vrai-faux",
                "question": "'World War II ended in 1945' is a true statement.",
                "correct": True,
                "explanation": "World War II ended in 1945 with the surrender of Germany and Japan."
            },
            {
                "id": "0008_7",
                "type": "qcm",
                "question": "Which of the following is a major river?",
                "options": ["Nile", "Amazon", "Mississippi", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "The Nile, Amazon, and Mississippi are all major rivers in the world."
            },
            {
                "id": "0008_8",
                "type": "vrai-faux",
                "question": "'The Industrial Revolution was a period of significant technological and economic change' is a true statement.",
                "correct": True,
                "explanation": "The Industrial Revolution was indeed a period of significant technological and economic change that began in the late 18th century."
            },
        ]
    ),
# ─── 0009 – Sciences : phénomènes naturels, corps humain, espace	 ─────────────────────
    (
        "0009",
        'sciences : phénomènes naturels, corps humain, espace',
        'Anglais',
        '6eme',
        [
            {
                "id": "0009_1",
                "type": "qcm",
                "question": "What is the largest planet in our solar system?",
                "options": ["Earth", "Mars", "Jupiter", "Saturn"],
                "correct_option": "Jupiter",
                "explanation": "Jupiter is the largest planet in our solar system."
            },
            {
                "id": "0009_2",
                "type": "vrai-faux",
                "question": "The human heart has four chambers.",
                "correct": True,
                "explanation": "The human heart indeed has four chambers: two atria and two ventricles."
            },
            {
                "id": "0009_3",
                "type": "qcm",
                "question": "Which of the following is a natural phenomenon?",
                "options": ["Earthquake", "Tornado", "Volcanic eruption", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Earthquakes, tornadoes, and volcanic eruptions are all natural phenomena."
            },
            {
                "id": "0009_4",
                "type": "vrai-faux",
                "question": "'The Milky Way is a galaxy that contains our solar system' is a true statement.",
                "correct": True,
                "explanation": "The Milky Way is indeed a galaxy that contains our solar system."
            },
            {
                "id": "0009_5",
                "type": "qcm",
                "question": "Which of the following is a state of matter?",
                "options": ["Solid", "Liquid", "Gas", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Solid, liquid, and gas are all states of matter."
            },
            {
                "id": "0009_6",
                "type": "vrai-faux",
                "question": "'Photosynthesis is the process by which plants convert sunlight into energy' is a true statement.",
                "correct": True,
                "explanation": "Photosynthesis is indeed the process by which plants convert sunlight into energy."
            },
            {
                "id": "0009_7",
                "type": "qcm",
                "question": "Which of the following is a part of the human digestive system?",
                "options": ["Stomach", "Liver", "Intestines", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "The stomach, liver, and intestines are all parts of the human digestive system."
            },
            {
                "id": "0009_8",
                "type": "vrai-faux",
                "question": "'The Earth revolves around the Sun' is a true statement.",
                "correct": True,
                "explanation": "The Earth does indeed revolve around the Sun, completing one orbit approximately every 365 days."
            },
        ]
    ),
# ─── 0010 – Langues : vocabulaire, expressions, communication	 ─────────────────────
    (
        "0010",
        'langues : vocabulaire, expressions, communication',
        'Anglais',
        '6eme',
        [
            {
                "id": "0010_1",
                "type": "qcm",
                "question": "What is the English word for 'chien'?",
                "options": ["Cat", "Dog", "Bird", "Fish"],
                "correct_option": "Dog",
                "explanation": "The English word for 'chien' is 'dog'."
            },
            {
                "id": "0010_2",
                "type": "vrai-faux",
                "question": "The phrase 'How are you?' is a common greeting in English.",
                "correct": True,
                "explanation": "The phrase 'How are you?' is indeed a common greeting used to ask about someone's well-being in English."
            },
            {
                "id": "0010_3",
                "type": "qcm",
                "question": "Which of the following is an expression of gratitude?",
                "options": ["Thank you", "Please", "Excuse me", "Sorry"],
                "correct_option": "Thank you",
                "explanation": "The expression 'Thank you' is used to express gratitude in English."
            },
            {
                "id": "0010_4",
                "type": "vrai-faux",
                "question": "'Goodbye' is a common way to say farewell in English.",
                "correct": True,
                "explanation": "'Goodbye' is indeed a common way to say farewell in English."
            },
            {
                "id": "0010_5",
                "type": "qcm",
                "question": "Which of the following is a synonym for 'happy'?",
                "options": ["Sad", "Angry", "Joyful", "Tired"],
                "correct_option": "Joyful",
                "explanation": "The word 'joyful' is a synonym for 'happy', meaning feeling or expressing great pleasure or happiness."
            },
            {
                "id": "0010_6",
                "type": "vrai-faux",
                "question": "'Please' is used to make a polite request in English.",
                "correct": True,
                "explanation": "The word 'Please' is indeed used to make polite requests in English."
            },
            {
                "id": "0010_7",
                "type": "qcm",
                "question": "Which of the following is an example of a question in English?",
                "options": ["What time is it?", "- What time is it?", "- It is what time?", "- Time what is it?"],
                "correct_option": "What time is it?",
                "explanation": "The sentence 'What time is it?' is an example of a question in English, as it is asking for information about the time."
            },
            {
                "id": "0010_8",
                "type": "vrai-faux",
                "question": "'Excuse me' is a phrase used to get someone's attention or to apologize in English.",
                "correct": True,
                "explanation": "The phrase 'Excuse me' is indeed used to get someone's attention or to apologize in English."
            },
        ]
    ),
#
    (
        "0011",
        'mathématiques : nombres, opérations, géométrie',
        'Anglais',
        '6eme',
        [
            {
                "id": "0011_1",
                "type": "qcm",
                "question": "What is the value of 5 + 3?",
                "options": ["6", "7", "8", "9"],
                "correct_option": "8",
                "explanation": "The value of 5 + 3 is 8."
            },
            {
                "id": "0011_2",
                "type": "vrai-faux",
                "question": "A triangle has three sides.",
                "correct": True,
                "explanation": "A triangle is a polygon with three edges and three vertices."
            },
            {
                "id": "0011_3",
                "type": "qcm",
                "question": "Which of the following is a prime number?",
                "options": ["4", "6", "7", "9"],
                "correct_option": "7",
                "explanation": "The number 7 is a prime number because it has only two distinct positive divisors: 1 and itself."
            },
            {
                "id": "0011_4",
                "type": "vrai-faux",
                "question": "'The area of a rectangle can be calculated by multiplying its length by its width' is a true statement.",
                "correct": True,
                "explanation": "The area of a rectangle is indeed calculated by multiplying its length by its width."
            },
            {
                "id": "0011_5",
                "type": "qcm",
                "question": "What is the value of 12 ÷ 4?",
                "options": ["2", "3", "4", "5"],
                "correct_option": "3",
                "explanation": "The value of 12 ÷ 4 is 3."
            },
            {
                "id": "0011_6",
                "type": "vrai-faux",
                "question": "'A square is a special type of rectangle' is a true statement.",
                "correct": True,
                "explanation": "A square is indeed a special type of rectangle where all sides are equal in length."
            },
            {
                "id": "0011_7",
                "type": "qcm",
                "question": "What is the value of 7 - 2?",
                "options": ["4", "5", "6", "7"],
                "correct_option": "5",
                "explanation": "The value of 7 - 2 is 5."
            },
            {
                "id": "0011_8",
                "type": "vrai-faux",
                "question": "'The sum of the angles in a triangle is 180 degrees' is a true statement.",
                "correct": True,
                "explanation": "The sum of the interior angles of a triangle is indeed 180 degrees."
            },
        ]
    ),
#─── 0012 – Histoire des arts : œuvres, artistes, mouvements artistiques	 ─────────────────────
    (
        "0012",
        'histoire des arts : œuvres, artistes, mouvements artistiques',
        'Anglais',
        '6eme',
        [
            {
                "id": "0012_1",
                "type": "qcm",
                "question": "Who painted the Mona Lisa?",
                "options": ["Leonardo da Vinci", "Vincent van Gogh", "Pablo Picasso", "Claude Monet"],
                "correct_option": "Leonardo da Vinci",
                "explanation": "The Mona Lisa was painted by Leonardo da Vinci."
            },
            {
                "id": "0012_2",
                "type": "vrai-faux",
                "question": "The Impressionist movement was characterized by a focus on light and color.",
                "correct": True,
                "explanation": "The Impressionist movement indeed emphasized the effects of light and color in art."
            },
            {
                "id": "0012_3",
                "type": "qcm",
                "question": "Which of the following is a famous sculpture?",
                "options": ["David", "The Starry Night", "The Persistence of Memory", "The Scream"],
                "correct_option": "David",
                "explanation": "'David' is a famous sculpture created by Michelangelo."
            },
            {
                "id": "0012_4",
                "type": "vrai-faux",
                "question": "'Pablo Picasso was a leading figure in the Cubist art movement' is a true statement.",
                "correct": True,
                "explanation": "Pablo Picasso was indeed a leading figure in the Cubist art movement, which he co-founded with Georges Braque."
            },
            {
                "id": "0012_5",
                "type": "qcm",
                "question": "Which of the following is a famous painting?",
                "options": ["The Last Supper", "The Thinker", "The Kiss", "The Birth of Venus"],
                "correct_option": "The Last Supper",
                "explanation": "'The Last Supper' is a famous painting created by Leonardo da Vinci."
            },
            {
                "id": "0012_6",
                "type": "vrai-faux",
                "question": "'Claude Monet was known for his landscape paintings' is a true statement.",
                "correct": True,
                "explanation": "Claude Monet was indeed known for his landscape paintings, particularly those depicting gardens and water lilies."
            },
            {
                "id": "0012_7",
                "type": "qcm",
                "question": "Which of the following is an art movement?",
                "options": ["Surrealism", "Realism", "Abstract Expressionism", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Surrealism, Realism, and Abstract Expressionism are all recognized art movements in the history of art."
            },
            {
                "id": "0012_8",
                "type": "vrai-faux",
                "question": "'The Scream is a famous painting by Edvard Munch' is a true statement.",
                "correct": True,
                "explanation": "'The Scream' is indeed a famous painting created by the Norwegian artist Edvard Munch."
            },
        ]
    ),
#─── 0013 – Géographie : pays, capitales, continents	 ─────────────────────
    (
        "0013",
        'géographie : pays, capitales, continents',
        'Anglais',
        '6eme',
        [
            {
                "id": "0013_1",
                "type": "qcm",
                "question": "What is the capital of France?",
                "options": ["Berlin", "Madrid", "Paris", "Rome"],
                "correct_option": "Paris",
                "explanation": "The capital of France is Paris."
            },
            {
                "id": "0013_2",
                "type": "vrai-faux",
                "question": "The Amazon Rainforest is located in South America.",
                "correct": True,
                "explanation": "The Amazon Rainforest is indeed located in South America, primarily in Brazil."
            },
            {
                "id": "0013_3",
                "type": "qcm",
                "question": "Which of the following is a continent?",
                "options": ["Asia", "Europe", "Africa", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Asia, Europe, and Africa are all continents."
            },
            {
                "id": "0013_4",
                "type": "vrai-faux",
                "question": "'The Nile is the longest river in the world' is a true statement.",
                "correct": True,
                "explanation": "'The Nile is indeed the longest river in the world, stretching over 6,650 kilometers (4,130 miles)."
            },
            {
                "id": "0013_5",
                "type": "qcm",
                "question": "What is the capital of Japan?",
                "options": ["Beijing", "Seoul", "Tokyo", "Bangkok"],
                "correct_option": "Tokyo",
                "explanation": "The capital of Japan is Tokyo."
            },
            {
                "id": "0013_6",
                "type": "vrai-faux",
                "question": "'Mount Everest is located in the Himalayas' is a true statement.",
                "correct": True,
                "explanation": "'Mount Everest is indeed located in the Himalayas, on the border between Nepal and China."
            },
            {
                "id": "0013_7",
                "type": "qcm",
                "question": "Which of the following countries is located in Europe?",
                "options": ["Italy", "France", "Germany", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Italy, France, and Germany are all countries located in Europe, known for their rich history, culture, and landmarks."
            },
            {
                "id": "0013_8",
                "type": "vrai-faux",
                "question": "'The Sahara Desert is the largest hot desert in the world' is a true statement.",
                "correct": True,
                "explanation": "'The Sahara Desert is indeed the largest hot desert in the world, covering much of North Africa."
            },
        ]
    ),
# ─── 0014 – Culture générale : cinéma, musique, littérature	 ─────────────
    (
        "0014",
        'culture générale : cinéma, musique, littérature',
        'Anglais',
        '6eme',
        [
            {
                "id": "0014_1",
                "type": "qcm",
                "question": "Who is the author of the Harry Potter series?",
                "options": ["J.K. Rowling", "Stephen King", "Roald Dahl", "Rick Riordan"],
                "correct_option": "J.K. Rowling",
                "explanation": "The Harry Potter series was written by J.K. Rowling."
            },
            {
                "id": "0014_2",
                "type": "vrai-faux",
                "question": "The movie 'The Lion King' was produced by Disney.",
                "correct": True,
                "explanation": "'The Lion King' is indeed a movie produced by Disney."
            },
            {
                "id": "0014_3",
                "type": "qcm",
                "question": "Which of the following is a musical instrument?",
                "options": ["Guitar", "Piano", "Drums", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "All of the listed options are musical instruments."
            },
            {
                "id": "0014_4",
                "type": "vrai-faux",
                "question": "'To Kill a Mockingbird' is a novel written by Harper Lee.",
                "correct": True,
                "explanation": "'To Kill a Mockingbird' is indeed a novel written by Harper Lee."
            },
            {
                "id": "0014_5",
                "type": "qcm",
                "question": "Which of the following movies was directed by Steven Spielberg?",
                "options": ["Jurassic Park", "E.T. the Extra-Terrestrial", "Jaws", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Steven Spielberg directed all of these movies: 'Jurassic Park', 'E.T. the Extra-Terrestrial', and 'Jaws'."
            },
            {
                "id": "0014_6",
                "type": "vrai-faux",
                "question": "'The Beatles' were a famous rock band from England.",
                "correct": True,
                "explanation": "'The Beatles' were indeed a famous rock band from England."
            },
            {
                "id": "0014_7",
                "type": "qcm",
                "question": "Which of the following is a genre of music?",
                "options": ["Rock", "Pop", "Jazz", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Rock, Pop, and Jazz are all genres of music, each with its own unique style and characteristics."
            },
            {
                "id": "0014_8",
                "type": "vrai-faux",
                "question": "'The Great Gatsby' is a novel written by F. Scott Fitzgerald.",
                "correct": True,
                "explanation": "'The Great Gatsby' is indeed a novel written by F. Scott Fitzgerald."
            },
        ]
    ),
#--
    (
        "0015",
        'sports : disciplines, événements, athlètes',
        'Anglais',
        '6eme',
        [
            {
                "id": "0015_1",
                "type": "qcm",
                "question": "Which of the following is a popular sport?",
                "options": ["Soccer", "Basketball", "Tennis", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Soccer, basketball, and tennis are all popular sports played around the world."
            },
            {
                "id": "0015_2",
                "type": "vrai-faux",
                "question": "The Olympic Games are held every four years.",
                "correct": True,
                "explanation": "The Olympic Games are indeed held every four years, featuring athletes from around the world competing in various sports."
            },
            {
                "id": "0015_3",
                "type": "qcm",
                "question": "Who is considered one of the greatest soccer players of all time?",
                "options": ["Pelé", "Lionel Messi", "Cristiano Ronaldo", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Pelé, Lionel Messi, and Cristiano Ronaldo are all considered among the greatest soccer players of all time."
            },
            {
                "id": "0015_4",
                "type": "vrai-faux",
                "question": "'The FIFA World Cup is an international soccer tournament' is a true statement.",
                "correct": True,
                "explanation": "The FIFA World Cup is indeed an international soccer tournament held every four years, where national teams compete for the title of world champion."
            },
            {
                "id": "0015_5",
                "type": "qcm",
                "question": "Which of the following is a type of tennis court surface?",
                "options": ["Clay", "Grass", "Hard", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Clay is one of the types of tennis court surfaces, along with grass and hard courts. Each surface has its own characteristics that affect how the ball bounces and how players perform."
            },
            {
                "id": "0015_6",
                "type": "vrai-faux",
                "question": "'Michael Jordan is widely regarded as one of the greatest basketball players of all time' is a true statement.",
                "correct": True,
                "explanation": "Michael Jordan is indeed widely regarded as one of the greatest basketball players of all time."
            },
        ]
    ),
#--
    (
        "0016",
        'musique : genres, instruments, artistes',
        'Anglais',
        '6eme',
        [
            {
                "id": "0016_1",
                "type": "qcm",
                "question": "Which of the following is a genre of music?",
                "options": ["Rock", "Classical", "Hip-hop", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Rock, Classical, and Hip-hop are all genres of music, each with its own unique style and characteristics."
            },
            {
                "id": "0016_2",
                "type": "vrai-faux",
                "question": "The guitar is a string instrument.",
                "correct": True,
                "explanation": "The guitar is indeed a string instrument, as it produces sound by vibrating strings."
            },
            {
                "id": "0016_3",
                "type": "qcm",
                "question": "Who is known as the 'King of Pop'?",
                "options": ["Elvis Presley", "Michael Jackson", "Prince", "Madonna"],
                "correct_option": "Michael Jackson",
                "explanation": "Michael Jackson is widely known as the 'King of Pop' due to his significant influence on the music industry and his record-breaking achievements."
            },
            {
                "id": "0016_4",
                "type": "vrai-faux",
                "question": "'Beethoven was a famous composer of classical music' is a true statement.",
                "correct": True,
                "explanation": "Ludwig van Beethoven was indeed a famous composer of classical music, known for his symphonies, sonatas, and concertos."
            },
            {
                "id": "0016_5",
                "type": "qcm",
                "question": "Which of the following is a wind instrument?",
                "options": ["Flute", "Saxophone", "Trumpet", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "The flute, saxophone, and trumpet are all wind instruments, as they produce sound by the vibration of air."
            },
            {
                "id": "0016_6",
                "type": "vrai-faux",
                "question": "'The Beatles were a famous rock band from England' is a true statement.",
                "correct": True,
                "explanation": "The Beatles were indeed a famous rock band from England, known for their influential music and cultural impact."
            },
            {
                "id": "0016_7",
                "type": "qcm",
                "question": "Which of the following is a famous music festival?",
                "options": ["Coachella", "Glastonbury", "Lollapalooza", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Coachella, Glastonbury, and Lollapalooza are all famous music festivals that attract large audiences and feature a wide range of musical acts."
            },
            {
                "id": "0016_8",
                "type": "vrai-faux",
                "question": "'Madonna is known as the 'Queen of Pop'' is a true statement.",
                "correct": True,
                "explanation": "Madonna is widely known as the 'Queen of Pop' due to her significant influence on the music industry and her record-breaking achievements."
            },
        ]
    ),
#--
    (
        "0017",
        'technologie : outils numériques, médias, réseaux sociaux',
        'Anglais',
        '6eme',
        [
                {
                    "id": "0017_1",
                    "type": "qcm",
                    "question": "Which of the following is a social media platform?",
                    "options": ["Facebook", "Twitter", "Instagram", "All of the above"],
                    "correct_option": "All of the above",
                    "explanation": "Facebook, Twitter, and Instagram are all popular social media platforms."
                },
                {
                    "id": "0017_2",
                    "type": "vrai-faux",
                    "question": "Using strong passwords can help protect your online accounts.",
                    "correct": True,
                    "explanation": "Strong passwords are important for keeping your online accounts secure from unauthorized access."
                },
                {
                    "id": "0017_3",
                    "type": "qcm",
                    "question": "Which of the following is a digital tool used for communication?",
                    "options": ["Email", "Text messaging", "Video conferencing", "All of the above"],
                    "correct_option": "All of the above",
                    "explanation": "Email, text messaging, and video conferencing are all digital tools that facilitate communication."
                },
                {
                    "id": "0017_4",
                    "type": "vrai-faux",
                    "question": "'Cyberbullying is a form of bullying that occurs online' is a true statement.",
                    "correct": True,
                    "explanation": "Cyberbullying involves using digital platforms to harass, threaten, or humiliate others."
                },
                {
                    "id": "0017_5",
                    "type": "qcm",
                    "question": "Which of the following is a benefit of using technology?",
                    "options": ["Access to information", "Improved communication", "Entertainment", "All of the above"],
                    "correct_option": "All of the above",
                    "explanation": "Technology provides access to information, improves communication, and offers various forms of entertainment."
                },
                {
                    "id": "0017_6",
                    "type": "vrai-faux",
                    "question": "'Spending too much time on screens can have negative effects on health' is a true statement.",
                    "correct": True,
                    "explanation": "Excessive screen time can lead to issues such as eye strain, sleep disturbances, and reduced physical activity."
                },
                {
                    "id": "0017_7",
                    "type": "qcm",
                    "question": "Which of the following is a digital citizenship skill?",
                    "options": ["Respecting others online", "Protecting personal information", "Using technology responsibly", "All of the above"],
                    "correct_option": "All of the above",
                    "explanation": "Digital citizenship involves respecting others online, protecting personal information, and using technology responsibly."
                },
                {
                    "id": "0017_8",
                    "type": "vrai-faux",
                    "question": "'The internet offers a wealth of information and entertainment options, making it a valuable resource when used responsibly' is a true statement.",
                    "correct": True,
                    "explanation": "The internet indeed provides a vast array of information and entertainment, but it is important to use it responsibly to avoid potential risks and negative consequences."
                },
            ]
    ),
#--
    (
        "0018",
        'histoire : événements, personnages, périodes historiques',
        'Anglais',
        '6eme',
        [
            {
                "id": "0018_1",
                "type": "qcm",
                "question": "Who was the first President of the United States?",
                "options": ["George Washington", "Abraham Lincoln", "Thomas Jefferson", "John Adams"],
                "correct_option": "George Washington",
                "explanation": "George Washington was the first President of the United States, serving from 1789 to 1797."
            },
            {
                "id": "0018_2",
                "type": "vrai-faux",
                "question": "The Great Wall of China was built to protect against invasions.",
                "correct": True,
                "explanation": "The Great Wall of China was indeed built to protect against invasions from nomadic tribes and military incursions."
            },
            {
                "id": "0018_3",
                "type": "qcm",
                "question": "Which of the following is a famous historical figure?",
                "options": ["Cleopatra", "Napoleon Bonaparte", "Mahatma Gandhi", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Cleopatra, Napoleon Bonaparte, and Mahatma Gandhi are all famous historical figures known for their significant impact on history."
            },
            {
                "id": "0018_4",
                "type": "vrai-faux",
                "question": "'The Renaissance was a period of cultural and artistic rebirth in Europe' is a true statement.",
                "correct": True,
                "explanation": "The Renaissance was indeed a period of cultural and artistic rebirth in Europe, spanning roughly from the 14th to the 17th century."
            },
            {
                "id": "0018_5",
                "type": "qcm",
                "question": "Which of the following is a major historical event?",
                "options": ["The American Revolution", "The French Revolution", "World War I", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "The American Revolution, the French Revolution, and World War I are all major historical events that had significant impacts on the course of history."
            },
            {
                "id": "0018_6",
                "type": "vrai-faux",
                "question": "'The Cold War was a period of political tension between the United States and the Soviet Union' is a true statement.",
                "correct": True,
                "explanation": "The Cold War was indeed a period of political tension between the United States and the Soviet Union, lasting from the end of World War II until the early 1990s."
            },
            {
                "id": "0018_7",
                "type": "qcm",
                "question": "Which of the following is a famous historical landmark?",
                "options": ["The Eiffel Tower", "The Great Pyramid of Giza", "The Statue of Liberty", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "The Eiffel Tower, The Great Pyramid of Giza, and The Statue of Liberty are all famous historical landmarks known for their architectural significance and cultural importance."
            },
            {
                "id": "0018_8",
                "type": "vrai-faux",
                "question": "'The Industrial Revolution was a period of significant technological and economic change' is a true statement.",
                "correct": True,
                "explanation": "The Industrial Revolution was indeed a period of significant technological and economic change that began in the late 18th century and transformed societies around the world."
            },
        ]
    ),
#--
    (
        "0019",
        'sciences : phénomènes naturels, corps humain, environnement',
        'Anglais',
        '6eme',
        [
            {
                "id": "0019_1",
                "type": "qcm",
                "question": "What is the process by which plants make their own food?",
                "options": ["Photosynthesis", "Respiration", "Digestion", "Fermentation"],
                "correct_option": "Photosynthesis",
                "explanation": "Photosynthesis is the process by which plants convert sunlight into energy, allowing them to produce their own food."
            },
            {
                "id": "0019_2",
                "type": "vrai-faux",
                "question": "The human heart is responsible for pumping blood throughout the body.",
                "correct": True,
                "explanation": "The human heart is indeed responsible for pumping blood throughout the body, delivering oxygen and nutrients to tissues and removing waste products."
            },
            {
                "id": "0019_3",
                "type": "qcm",
                "question": "Which of the following is a natural phenomenon?",
                "options": ["Earthquake", "Tornado", "Volcanic eruption", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Earthquakes, tornadoes, and volcanic eruptions are all natural phenomena that occur due to geological and atmospheric processes."
            },
            {
                "id": "0019_4",
                "type": "vrai-faux",
                "question": "'Global warming is caused by an increase in greenhouse gases in the atmosphere' is a true statement.",
                "correct": True,
                "explanation": "Global warming is indeed caused by an increase in greenhouse gases, such as carbon dioxide, in the atmosphere, which trap heat and lead to rising global temperatures."
            },
            {
                "id": "0019_5",
                "type": "qcm",
                "question": "Which of the following is a part of the human respiratory system?",
                "options": ["Lungs", "Heart", "Liver", "Kidneys"],
                "correct_option": "Lungs",
                "explanation": "The lungs are a crucial part of the human respiratory system, responsible for the exchange of oxygen and carbon dioxide."
            },
            {
                "id": "0019_6",
                "type": "vrai-faux",
                "question": "'The water cycle includes processes such as evaporation, condensation, and precipitation' is a true statement.",
                "correct": True,
                "explanation": "The water cycle indeed includes processes such as evaporation (water turning into vapor), condensation (vapor cooling and forming clouds), and precipitation (water falling back to the Earth as rain, snow, etc.)."
            },
            {
                "id": "0019_7",
                "type": "qcm",
                "question": "Which of the following is a renewable energy source?",
                "options": ["Solar power", "Wind power", "Hydropower", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Solar power, wind power, and hydropower are all renewable energy sources that can be replenished naturally and have a lower environmental impact compared to fossil fuels."
            },
            {
                "id": "0019_8",
                "type": "vrai-faux",
                "question": "'Recycling helps reduce waste and conserve natural resources' is a true statement.",
                "correct": True,
                "explanation": "Recycling is indeed an important practice that helps reduce waste, conserve natural resources, and minimize environmental impact by reusing materials instead of discarding them."
            },
        ]
    ),
#--
    (
        "0020",
        'langue anglaise : vocabulaire, grammaire, expressions courantes',
        'Anglais',
        '6eme',
        [
            {
                "id": "0020_1",
                "type": "qcm",
                "question": "What is the English word for 'chien'?",
                "options": ["Cat", "Dog", "Bird", "Fish"],
                "correct_option": "Dog",
                "explanation": "The English word for 'chien' is 'dog'."
            },
            {
                "id": "0020_2",
                "type": "vrai-faux",
                "question": "'I am' is the correct way to say 'je suis' in English.",
                "correct": True,
                "explanation": "'I am' is indeed the correct way to say 'je suis' in English."
            },
            {
                "id": "0020_3",
                "type": "qcm",
                "question": "Which of the following is a common English greeting?",
                "options": ["Hello", "Goodbye", "Thank you", "Please"],
                "correct_option": "Hello",
                "explanation": "'Hello' is a common English greeting used to say hi or welcome someone."
            },
            {
                "id": "0020_4",
                "type": "vrai-faux",
                "question": "'The cat is on the table' is a grammatically correct sentence in English.",
                "correct": True,
                "explanation": "'The cat is on the table' is indeed a grammatically correct sentence in English, as it follows the subject-verb-object structure and uses proper prepositions."
            },
            {
                "id": "0020_5",
                "type": "qcm",
                "question": "Which of the following words is an adjective?",
                "options": ["Happy", "Run", "Quickly", "Table"],
                "correct_option": "Happy",
                "explanation": "'Happy' is an adjective because it describes a noun (a person, place, or thing) by expressing a quality or state of being."
            },
            {
                "id": "0020_6",
                "type": "vrai-faux",
                "question": "'Thank you' is a phrase used to express gratitude in English' is a true statement.",
                "correct": True,
                "explanation": "'Thank you' is indeed a phrase used to express gratitude in English."
            },
            {
                "id": "0020_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression for saying goodbye?",
                "options": ["See you later", "Good morning", "How are you?", "Thank you"],
                "correct_option": "See you later",
                "explanation": "'See you later' is a common English expression used to say goodbye or indicate that you will see someone again in the future."
            },
            {
                "id": "0020_8",
                "type": "vrai-faux",
                "question": "'The dog is barking loudly' is a grammatically correct sentence in English' is a true statement.",
                "correct": True,
                "explanation": "'The dog is barking loudly' is indeed a grammatically correct sentence in English, as it follows the subject-verb-adverb structure and uses proper verb tense."
            },
        ]
    ),
    (
        "0021",
        'Famille, environnement, actualité simple',
        "Anglais",
        "6ème",
        [
            {
                "id": "0021_1",
                "type": "qcm",
                "question": "What is the English word for 'famille'?",
                "options": ["Family", "Friend", "House", "School"],
                "correct_option": "Family",
                "explanation": "The English word for 'famille' is 'family'."
            },
            {
                "id": "0021_2",
                "type": "vrai-faux",
                "question": "'The environment is important to protect' is a true statement.",
                "correct": True,
                "explanation": "The environment is indeed important to protect, as it provides essential resources and habitats for all living organisms."
            },
            {
                "id": "0021_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'environnement'?",
                "options": ["Environment", "Nature", "Earth", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Environment', 'Nature', and 'Earth' are all common English words that can be used to refer to the natural world and the surroundings in which we live."
            },
            {
                "id": "0021_4",
                "type": "vrai-faux",
                "question": "'Recycling helps reduce waste and conserve natural resources' is a true statement.",
                "correct": True,
                "explanation": "Recycling is indeed an important practice that helps reduce waste, conserve natural resources, and minimize environmental impact by reusing materials instead of discarding them."
            },
            {
                "id": "0021_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'actualité'?",
                "options": ["News", "Current events", "Headlines", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'News', 'Current events', and 'Headlines' are all common English words that can be used to refer to recent or ongoing events and developments in the world."
            },
            {
                "id": "0021_6",
                "type": "vrai-faux",
                "question": "'Climate change is a significant global issue' is a true statement.",
                "correct": True,
                "explanation": "'Climate change is indeed a significant global issue that affects ecosystems, weather patterns, and human societies around the world."
            },
            {
                "id": "0021_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'simple'?",
                "options": ["Simple", "Easy", "Basic", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Simple', 'Easy', and 'Basic' are all common English words that can be used to describe something that is not complicated or difficult to understand."
            },
            {
                "id": "0021_8",
                "type": "vrai-faux",
                "question": "'The internet provides access to a wealth of information and resources' is a true statement.",
                "correct": True,
                "explanation": "The internet indeed provides access to a vast array of information and resources, making it a valuable tool for learning, communication, and entertainment when used responsibly."
            },
        ]
    ),
    (
        "0022",
        'Vocabulaire de base : couleurs, nombres, jours de la semaine',
        "Anglais",
        "6ème",
        [
            {
                "id": "0022_1",
                "type": "qcm",
                "question": "What is the English word for 'rouge'?",
                "options": ["Red", "Blue", "Green", "Yellow"],
                "correct_option": "Red",
                "explanation": "The English word for 'rouge' is 'red'."
            },
            {
                "id": "0022_2",
                "type": "vrai-faux",
                "question": "'The number 5 is greater than the number 3' is a true statement.",
                "correct": True,
                "explanation": "The number 5 is indeed greater than the number 3, as it represents a larger quantity."
            },
            {
                "id": "0022_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'nombres'?",
                "options": ["Numbers", "Digits", "Numerals", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Numbers', 'Digits', and 'Numerals' are all common English words that can be used to refer to numerical symbols and quantities."
            },
            {
                "id": "0022_4",
                "type": "vrai-faux",
                "question": "'The number 10 is less than the number 20' is a true statement.",
                "correct": True,
                "explanation": "The number 10 is indeed less than the number 20, as it represents a smaller quantity."
            },
            {
                "id": "0022_5",
                "type": "qcm",
                "question": "What is the English word for 'lundi'?",
                "options": ["Monday", "Tuesday", "Wednesday", "Thursday"],
                "correct_option": "Monday",
                "explanation": "The English word for 'lundi' is 'Monday'."
            },
            {
                "id": "0022_6",
                "type": "vrai-faux",
                "question": "'The week has seven days' is a true statement.",
                "correct": True,
                "explanation": "The week indeed has seven days: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, and Sunday."
            },
            {
                "id": "0022_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'couleurs'?",
                "options": ["Colors", "Hues", "Shades", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Colors', 'Hues', and 'Shades' are all common English words that can be used to refer to different aspects of color and visual perception."
            },
            {
                "id": "0022_8",
                "type": "vrai-faux",
                "question": "'The color blue is often associated with calmness and tranquility' is a true statement.",
                "correct": True,
                "explanation": "The color blue is indeed often associated with calmness and tranquility, as it can evoke feelings of peace and relaxation."
            },
        ]
    ),
    (
        "0023",
        'Expressions courantes : salutations, formules de politesse, phrases simples',
        "Anglais",
        "6ème",
        [
            {
                "id": "0023_1",
                "type": "qcm",
                "question": "Which of the following is a common English greeting?",
                "options": ["Hello", "Goodbye", "Thank you", "Please"],
                "correct_option": "Hello",
                "explanation": "'Hello' is a common English greeting used to say hi or welcome someone."
            },             {
                "id": "0023_2",
                "type": "vrai-faux",
                "question": "'Thank you' is a phrase used to express gratitude in English' is a true statement.",
                "correct": True,
                "explanation": "'Thank you' is indeed a phrase used to express gratitude in English."
                },
            {
                "id": "0023_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression for saying goodbye?",
                "options": ["See you later", "Good morning", "How are you?", "Thank you"],
                "correct_option": "See you later",
                "explanation": "'See you later' is a common English expression used to say goodbye or indicate that you will see someone again in the future."
            },
            {
                "id": "0023_4",
                "type": "vrai-faux",
                "question": "'Please' is a word used to make requests more polite in English' is a true statement.",
                "correct": True,
                "explanation": "'Please' is indeed a word used to make requests more polite in English."
            },
            {
                "id": "0023_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression for asking how someone is doing?",
                "options": ["How are you?", "What's up?", "How's it going?", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'How are you?', 'What's up?', and 'How's it going?' are all common English expressions used to ask how someone is doing or feeling."
            },
            {
                "id": "0023_6",
                "type": "vrai-faux",
                "question": "'Excuse me' is a phrase used to get someone's attention or apologize in English' is a true statement.",
                "correct": True,
                "explanation": "'Excuse me' is indeed a phrase used to get someone's attention or apologize in English."
            },
            {
                "id": "0023_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression for showing appreciation?",
                "options": ["Thank you", "You're welcome", "Great job", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Thank you', 'You're welcome', and 'Great job' are all common English expressions used to show appreciation."
            },
            {
                "id": "0023_8",
                "type": "vrai-faux",
                "question": "'The phrase 'Have a nice day' is a common way to wish someone well in English' is a true statement.",
                "correct": True,
                "explanation": "'Have a nice day' is indeed a common way to wish someone well in English."
            },
        ]
    ),
    (
        "0024",
        'Sports : sports populaires, événements sportifs, athlètes célèbres',
        "Anglais",
        "6ème",
        [
            {
                "id": "0024_1",
                "type": "qcm",
                "question": "Which of the following is a popular sport?",
                "options": ["Soccer", "Basketball", "Tennis", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Soccer, basketball, and tennis are all popular sports played and watched by millions of people around the world."
            },
            {
                "id": "0024_2",
                "type": "vrai-faux",
                "question": "'The Olympic Games are held every four years' is a true statement.",
                "correct": True,
                "explanation": "The Olympic Games are indeed held every four years, bringing together athletes from around the world to compete in various sports."
            },
            {
                "id": "0024_3",
                "type": "qcm",
                "question": "Who is known as the 'King of Soccer'?",
                "options": ["Pelé", "Lionel Messi", "Cristiano Ronaldo", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Pelé, Lionel Messi, and Cristiano Ronaldo are all considered among the greatest soccer players of all time, often referred to as the 'King of Soccer' by fans and experts alike."
            },
            {
                "id": "0024_4",
                "type": "vrai-faux",
                "question": "'The FIFA World Cup is the most prestigious international soccer tournament' is a true statement.",
                "correct": True,
                "explanation": "The FIFA World Cup is indeed the most prestigious international soccer tournament, held every four years and featuring teams from around the world competing for the title of world champion."
            },
            {
                "id": "0024_5",
                "type": "qcm",
                "question": "Which of the following is a famous sporting event?",
                "options": ["Super Bowl", "Wimbledon", "Tour de France", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "The Super Bowl, Wimbledon, and the Tour de France are all famous sporting events that attract large audiences and feature top athletes in their respective sports."
            },
            {
                "id": "0024_6",
                "type": "vrai-faux",
                "question": "'Serena Williams is a famous tennis player' is a true statement.",
                "correct": True,
                "explanation": "Serena Williams is indeed a famous tennis player, known for her powerful playing style and numerous Grand Slam titles."
            },
            {
                "id": "0024_7",
                "type": "qcm",
                "question": "Which of the following is a popular winter sport?",
                "options": ["Skiing", "Snowboarding", "Ice skating", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Skiing, snowboarding, and ice skating are all popular winter sports enjoyed by people of all ages around the world."
            },
            {
                "id": "0024_8",
                "type": "vrai-faux",
                "question": "'Michael Jordan is considered one of the greatest basketball players of all time' is a true statement.",
                "correct": True,
                "explanation": "Michael Jordan is indeed considered one of the greatest basketball players of all time, known for his incredible skill, competitive spirit, and numerous championships with the Chicago Bulls."
            },
            ]
    ),
    (
        "0025",
        'Temps : saisons, météo, phénomènes météorologiques',
        "Anglais",
        "6ème",
        [
            {
                "id": "0025_1",
                "type": "qcm",
                "question": "Which of the following is a season?",
                "options": ["Spring", "Summer", "Autumn", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Spring, summer, and autumn are all seasons that occur throughout the year, each with its own unique weather patterns and characteristics."
            },
            {
                "id": "0025_2",
                "type": "vrai-faux",
                "question": "'The weather can change from day to day' is a true statement.",
                "correct": True,
                "explanation": "The weather can indeed change from day to day due to various atmospheric conditions and factors."
            },
            {
                "id": "0025_3",
                "type": "qcm",
                "question": "Which of the following is a common weather phenomenon?",
                "options": ["Rain", "Snow", "Thunderstorms", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Rain, snow, and thunderstorms are all common weather phenomena that can occur in different regions and seasons."
            },
            {
                "id": "0025_4",
                "type": "vrai-faux",
                "question": "'The sun is a star that provides light and heat to the Earth' is a true statement.",
                "correct": True,
                "explanation": "The sun is indeed a star that provides light and heat to the Earth, making it essential for life and influencing weather patterns."
            },
            {
                "id": "0025_5",
                "type": "qcm",
                "question": "Which of the following is a common weather condition during winter?",
                "options": ["Snow", "Ice", "Cold temperatures", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Snow, ice, and cold temperatures are all common weather conditions that can occur during winter in many regions around the world."
            },
            {
                "id": "0025_6",
                "type": "vrai-faux",
                "question": "'A tornado is a violent rotating column of air that extends from a thunderstorm to the ground' is a true statement.",
                "correct": True,
                "explanation": "A tornado is indeed a violent rotating column of air that extends from a thunderstorm to the ground, capable of causing significant damage."
            },
            {
                "id": "0025_7",
                "type": "qcm",
                "question": "Which of the following is a common weather phenomenon during summer?",
                "options": ["Heatwaves", "Thunderstorms", "Droughts", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "Heatwaves, thunderstorms, and droughts are all common weather phenomena that can occur during summer in various regions around the world."
            },
            {
                "id": "0025_8",
                "type": "vrai-faux",
                "question": "'Climate change can lead to more extreme weather events' is a true statement.",
                "correct": True,
                "explanation": "'Climate change can indeed lead to more extreme weather events, such as stronger storms, heatwaves, and changes in precipitation patterns."
            },
        ]
    ),
    (
        "0026",
        'Conjuguaison : verbes réguliers, verbes irréguliers, temps de base',
        "Anglais",
        "6ème",
        [
            {
                "id": "0026_1",
                "type": "qcm",
                "question": "Which of the following is a regular verb in English?",
                "options": ["Walk", "Go", "Eat", "Have"],
                "correct_option": "Walk",
                "explanation": "A regular verb in English is one that forms its past tense and past participle by adding -ed to the base form. 'Walk' is a regular verb, while 'Go', 'Eat', and 'Have' are irregular verbs."
            },
            {
                "id": "0026_2",
                "type": "vrai-faux",
                "question": "'The past tense of 'walk' is 'walked'' is a true statement.",
                "correct": True,
                "explanation": "The past tense of 'walk' is indeed 'walked', following the regular verb conjugation pattern."
            },
            {
                "id": "0026_3",
                "type": "qcm",
                "question": "Which of the following is an irregular verb in English?",
                "options": ["Run", "Jump", "Play", "Talk"],
                "correct_option": "Run",
                "explanation": "An irregular verb in English is one that does not follow the regular conjugation pattern. 'Run' is an irregular verb, while 'Jump', 'Play', and 'Talk' are regular verbs."
            },
            {
                "id": "0026_4",
                "type": "vrai-faux",
                "question": "'The past tense of 'run' is 'ran'' is a true statement.",
                "correct": True,
                "explanation": "The past tense of 'run' is indeed 'ran', which is an irregular conjugation."
            },
            {
                "id": "0026_5",
                "type": "qcm",
                "question": "Which of the following is the correct past tense form of 'eat'?",
                "options": ["Eated", "Eaten", "Ate", "Eat"],
                "correct_option": "Ate",
                "explanation": "The correct past tense form of 'eat' is 'ate', which is an irregular verb conjugation."
            },
            {
                "id": "0026_6",
                "type": "vrai-faux",
                "question": "'The past tense of 'have' is 'had'' is a true statement.",
                "correct": True,
                "explanation": "The past tense of 'have' is indeed 'had', which is an irregular verb conjugation."
            },
            {
                "id": "0026_7",
                "type": "qcm",
                "question": "Which of the following is the correct past participle form of 'go'?",
                "options": ["Goed", "Gone", "Went", "Go"],
                "correct_option": "Gone",
                "explanation": "The correct past participle form of 'go' is 'gone', which is an irregular verb conjugation."
            },
            {
                "id": "0026_8",
                "type": "vrai-faux",
                "question": "'The past participle of 'go' is 'gone'' is a true statement.",
                "correct": True,
                "explanation": "The past participle of 'go' is indeed 'gone', which is used in perfect tenses and passive voice."
            },
            ]
    ),
    (
        "0027",
        'Vocabulaire de base : animaux, aliments, objets du quotidien',
        "Anglais",
        "6ème",
        [
            {
                "id": "0027_1",
                "type": "qcm",
                "question": "What is the English word for 'chat'?",
                "options": ["Cat", "Dog", "Bird", "Fish"],
                "correct_option": "Cat",
                "explanation": "The English word for 'chat' is 'cat'."
            },
            {
                "id": "0027_2",
                "type": "vrai-faux",
                "question": "'The English word for 'pomme' is 'apple'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'pomme' is indeed 'apple'."
            },
            {
                "id": "0027_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'objets du quotidien'?",
                "options": ["Everyday objects", "Household items", "Daily essentials", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Everyday objects', 'Household items', and 'Daily essentials' are all common English phrases that can be used to refer to objects that are commonly used in daily life."
            },
            {
                "id": "0027_4",
                "type": "vrai-faux",
                "question": "'The English word for 'chien' is 'dog'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'chien' is indeed 'dog'."
            },
            {
                "id": "0027_5",
                "type": "qcm",
                "question": "What is the English word for 'fromage'?",
                "options": ["Cheese", "Bread", "Milk", "Butter"],
                "correct_option": "Cheese",
                "explanation": "The English word for 'fromage' is 'cheese'."
            },
            {
                "id": "0027_6",
                "type": "vrai-faux",
                "question": "'The English word for 'maison' is 'house'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'maison' is indeed 'house'."
            },
            {
                "id": "0027_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'aliments'?",
                "options": ["Food", "Groceries", "Meals", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Food', 'Groceries', and 'Meals' are all common English words that can be used to refer to items that are consumed for nourishment."
            },
            {
                "id": "0027_8",
                "type": "vrai-faux",
                "question": "'The English word for 'voiture' is 'car'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'voiture' is indeed 'car'."
            },
        ]
    ),
    (
        "0028",
        'Grammaire de base : articles, prépositions, pronoms personnels',
        "Anglais",
        "6ème",
        [
            {
                "id": "0028_1",
                "type": "qcm",
                "question": "Which of the following is a definite article in English?",
                "options": ["A", "An", "The", "All of the above"],
                "correct_option": "The",
                "explanation": "The definite article in English is 'the', which is used to refer to specific nouns that are known to the speaker and listener."
            },
            {
                "id": "0028_2",
                "type": "vrai-faux",
                "question": "'The indefinite articles in English are 'a' and 'an'' is a true statement.",
                "correct": True,
                "explanation": "The indefinite articles in English are indeed 'a' and 'an', which are used to refer to non-specific nouns."
            },
            {
                "id": "0028_3",
                "type": "qcm",
                "question": "Which of the following is a common English preposition?",
                "options": ["In", "On", "At", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In', 'On', and 'At' are all common English prepositions that indicate location, time, or direction."
            },
            {
                "id": "0028_4",
                "type": "vrai-faux",
                "question": "'The preposition 'in' is used to indicate location inside something' is a true statement.",
                "correct": True,
                "explanation": "The preposition 'in' is indeed used to indicate location inside something, such as 'The book is in the bag.'"
            },
            {
                "id": "0028_5",
                "type": "qcm",
                "question": "Which of the following is a common English personal pronoun?",
                "options": ["I", "You", "He", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'I', 'You', and 'He' are all common English personal pronouns that are used to refer to specific people or things in a sentence."
            },
            {
                "id": "0028_6",
                "type": "vrai-faux",
                "question": "'The personal pronoun 'she' is used to refer to a female person' is a true statement.",
                "correct": True,
                "explanation": "The personal pronoun 'she' is indeed used to refer to a female person."
            },
            {
                "id": "0028_7",
                "type": "qcm",
                "question": "Which of the following is a common English possessive pronoun?",
                "options": ["My", "Your", "His", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'My', 'Your', and 'His' are all common English possessive pronouns that are used to indicate ownership or possession."
            },
            {
                "id": "0028_8",
                "type": "vrai-faux",
                "question": "'The possessive pronoun 'our' is used to refer to something that belongs to us' is a true statement.",
                "correct": True,
                "explanation": "The possessive pronoun 'our' is indeed used to refer to something that belongs to us, such as 'This is our house.'"
            },
        ]
    ),
    (
        "0029",
        'Vocabulaire de base : famille, école, loisirs',
        "Anglais",
        "6ème",
        [
            {
                "id": "0029_1",
                "type": "qcm",
                "question": "What is the English word for 'père'?",
                "options": ["Father", "Mother", "Brother", "Sister"],
                "correct_option": "Father",
                "explanation": "The English word for 'père' is 'father'."
            },
            {
                "id": "0029_2",
                "type": "vrai-faux",
                "question": "'The English word for 'mère' is 'mother'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'mère' is indeed 'mother'."
            },
            {
                "id": "0029_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'école'?",
                "options": ["School", "Classroom", "Education", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'School', 'Classroom', and 'Education' are all common English words that can be used to refer to the concept of school and learning."
            },
            {
                "id": "0029_4",
                "type": "vrai-faux",
                "question": "'The English word for 'loisirs' is 'hobbies'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'loisirs' is indeed 'hobbies', which refers to activities that people enjoy doing in their free time."
            },
            {
                "id": "0029_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'frère'?",
                "options": ["Brother", "Sister", "Cousin", "Uncle"],
                "correct_option": "Brother",
                "explanation": "The English word for 'frère' is 'brother'."
            },
            {
                "id": "0029_6",
                "type": "vrai-faux",
                "question": "'The English word for 'soeur' is 'sister'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'soeur' is indeed 'sister'."
            },
            {
                "id": "0029_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'loisirs'?",
                "options": ["Hobbies", "Leisure activities", "Pastimes", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Hobbies', 'Leisure activities', and 'Pastimes' are all common English words that can be used to refer to activities that people enjoy doing in their free time."
            },
            {
                "id": "0029_8",
                "type": "vrai-faux",
                "question": "'The English word for 'école' is 'school'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'école' is indeed 'school', which refers to an institution where students receive education and learn various subjects."
            },
        ]
    ),
    (
        "0030",
        'Vocabulaire de base : famille, école, loisirs',
        "Anglais",
        "6ème",
        [
            {
                "id": "0030_1",
                "type": "qcm",
                "question": "What is the English word for 'père'?",
                "options": ["Father", "Mother", "Brother", "Sister"],
                "correct_option": "Father",
                "explanation": "The English word for 'père' is 'father'."
            },
            {
                "id": "0030_2",
                "type": "vrai-faux",
                "question": "'The English word for 'mère' is 'mother'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'mère' is indeed 'mother'."
            },
            {
                "id": "0030_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'école'?",
                "options": ["School", "Classroom", "Education", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'School', 'Classroom', and 'Education' are all common English words that can be used to refer to the concept of school and learning."
            },
            {
                "id": "0030_4",
                "type": "vrai-faux",
                "question": "'The English word for 'loisirs' is 'hobbies'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'loisirs' is indeed 'hobbies', which refers to activities that people enjoy doing in their free time."
            },
            {
                "id": "0030_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'frère'?",
                "options": ["Brother", "Sister", "Cousin", "Uncle"],
                "correct_option": "Brother",
                "explanation": "The English word for 'frère' is 'brother'."
            },
            {
                "id": "0030_6",
                "type": "vrai-faux",
                "question": "'The English word for 'soeur' is 'sister'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'soeur' is indeed 'sister'."
            },
            {
                "id": "0030_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'loisirs'?",
                "options": ["Hobbies", "Leisure activities", "Pastimes", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Hobbies', 'Leisure activities', and 'Pastimes' are all common English words that can be used to refer to activities that people enjoy doing in their free time."
            },
            {
                "id": "0030_8",
                "type": "vrai-faux",
                "question": "'The English word for 'école' is 'school'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'école' is indeed 'school', which refers to an institution where students receive education and learn various subjects."
            },
         ]
    ),
    (
        "0031",
        'Vocabulaire de base : école, loisirs',
        "Anglais",
        "6ème",
        [
            {
                "id": "0031_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'école'?",
                "options": ["School", "Classroom", "Education", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'School', 'Classroom', and 'Education' are all common English words that can be used to refer to the concept of school and learning."
            },
            {
                "id": "0031_2",
                "type": "vrai-faux",
                "question": "'The English word for 'loisirs' is 'hobbies'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'loisirs' is indeed 'hobbies', which refers to activities that people enjoy doing in their free time."
            },
            {
                "id": "0031_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'loisirs'?",
                "options": ["Hobbies", "Leisure activities", "Pastimes", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Hobbies', 'Leisure activities', and 'Pastimes' are all common English words that can be used to refer to activities that people enjoy doing in their free time."
            },
            {
                "id": "0031_4",
                "type": "vrai-faux",
                "question": "'The English word for 'école' is 'school'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'école' is indeed 'school', which refers to an institution where students receive education and learn various subjects."
            },
            {
                "id": "0031_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'école'?",
                "options": ["School", "University", "College", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'School', 'University', and 'College' are all common English words that can be used to refer to different types of educational institutions."
            },
            {
                "id": "0031_6",
                "type": "vrai-faux",
                "question": "'The English word for 'loisirs' is 'leisure activities'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'loisirs' can indeed be translated as 'leisure activities', which refers to activities that people enjoy doing in their free time."
            },
            {
                "id": "0031_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'école'?",
                "options": ["School", "Academy", "Institute", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'School', 'Academy', and 'Institute' are all common English words that can be used to refer to different types of educational institutions."
            },
            {
                "id": "0031_8",
                "type": "vrai-faux",
                "question": "'The English word for 'loisirs' is 'pastimes'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'loisirs' can indeed be translated as 'pastimes', which refers to activities that people enjoy doing in their free time."
            },
        ]
    ),
    (
        "0032",
        'Vocabulaire de base : objets scolaires, objets du quotidien',
        "Anglais",
        "6ème",
        [
            {
                "id": "0032_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'stylo'?",
                "options": ["Pen", "Pencil", "Marker", "All of the above"],
                "correct_option": "Pen",
                "explanation": "The English word for 'stylo' is 'pen', which is a common writing instrument used for writing or drawing."
            },
            {
                "id": "0032_2",
                "type": "vrai-faux",
                "question": "'The English word for 'cahier' is 'notebook'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'cahier' is indeed 'notebook', which is a common item used for writing notes, drawing, or organizing information."
            },
            {
                "id": "0032_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression for saying goodbye?",
                "options": ["Goodbye", "See you later", "Take care", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Goodbye', 'See you later', and 'Take care' are all common English expressions used to say goodbye or indicate that you will see someone again in the future."
            },
            {
                "id": "0032_4",
                "type": "vrai-faux",
                "question": "'The English word for 'gomme' is 'eraser'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'gomme' is indeed 'eraser', which is a common item used to remove pencil marks from paper."
            },
            {
                "id": "0032_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'sac à dos'?",
                "options": ["Backpack", "Bag", "Purse", "All of the above"],
                "correct_option": "Backpack",
                "explanation": "The English word for 'sac à dos' is 'backpack', which is a common item used to carry books, supplies, and personal belongings, especially by students."
            },
            {
                "id": "0032_6",
                "type": "vrai-faux",
                "question": "'The English word for 'bureau' is 'desk'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'bureau' is indeed 'desk', which is a common piece of furniture used for writing, working, or studying."
            },
            {
                "id": "0032_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'règle'?",
                "options": ["Ruler", "Tape measure", "Measuring stick", "All of the above"],
                "correct_option": "Ruler",
                "explanation": "The English word for 'règle' is 'ruler', which is a common item used to measure length or draw straight lines."
            },
            {
                "id": "0032_8",
                "type": "vrai-faux",
                "question": "'The English word for 'chaise' is 'chair'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'chaise' is indeed 'chair', which is a common piece of furniture used for sitting."
            },
        ]
    ),
    (
        "0033",
        'Vocabulaire de base : objets scolaires, objets du quotidien',
        "Anglais",
        "6ème",
        [
            {
                "id": "0033_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'stylo'?",
                "options": ["Pen", "Pencil", "Marker", "All of the above"],
                "correct_option": "Pen",
                "explanation": "The English word for 'stylo' is 'pen', which is a common writing instrument used for writing or drawing."
            },
            {
                "id": "0033_2",
                "type": "vrai-faux",
                "question": "'The English word for 'cahier' is 'notebook'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'cahier' is indeed 'notebook', which is a common item used for writing notes, drawing, or organizing information."
            },
            {
                "id": "0033_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression for saying goodbye?",
                "options": ["Goodbye", "See you later", "Take care", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Goodbye', 'See you later', and 'Take care' are all common English expressions used to say goodbye or indicate that you will see someone again in the future."
            },
            {
                "id": "0033_4",
                "type": "vrai-faux",
                "question": "'The English word for 'gomme' is 'eraser'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'gomme' is indeed 'eraser', which is a common item used to remove pencil marks from paper."
            },
            {
                "id": "0033_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'sac à dos'?",
                "options": ["Backpack", "Bag", "Purse", "All of the above"],
                "correct_option": "Backpack",
                "explanation": "The English word for 'sac à dos' is 'backpack', which is a common item used to carry books, supplies, and personal belongings, especially by students."
            },
            {
                "id": "0033_6",
                "type": "vrai-faux",
                "question": "'The English word for 'bureau' is 'desk'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'bureau' is indeed 'desk', which is a common piece of furniture used for writing, working, or studying."
            },
            {
                "id": "0033_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'règle'?",
                "options": ["Ruler", "Tape measure", "Measuring stick", "All of the above"],
                "correct_option": "Ruler",
                "explanation": "The English word for 'règle' is 'ruler', which is a common item used to measure length or draw straight lines."
            },
            {
                "id": "0033_8",
                "type": "vrai-faux",
                "question": "'The English word for 'chaise' is 'chair'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'chaise' is indeed 'chair', which is a common piece of furniture used for sitting."
            },
        ]
    ),
    (
        "0034",
        'Vocabulaire de base : objets scolaires, objets du quotidien',
        "Anglais",
        "6ème",
        [
            {
                "id": "0034_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'stylo'?",
                "options": ["Pen", "Pencil", "Marker", "All of the above"],
                "correct_option": "Pen",
                "explanation": "The English word for 'stylo' is 'pen', which is a common writing instrument used for writing or drawing."
            },
            {
                "id": "0034_2",
                "type": "vrai-faux",
                "question": "'The English word for 'cahier' is 'notebook'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'cahier' is indeed 'notebook', which is a common item used for writing notes, drawing, or organizing information."
            },
            {
                "id": "0034_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression for saying goodbye?",
                "options": ["Goodbye", "See you later", "Take care", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Goodbye', 'See you later', and 'Take care' are all common English expressions used to say goodbye or indicate that you will see someone again in the future."
            },
            {
                "id": "0034_4",
                "type": "vrai-faux",
                "question": "'The English word for 'gomme' is 'eraser'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'gomme' is indeed 'eraser', which is a common item used to remove pencil marks from paper."
            },
            {
                "id": "0034_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'sac à dos'?",
                "options": ["Backpack", "Bag", "Purse", "All of the above"],
                "correct_option": "Backpack",
                "explanation": "The English word for 'sac à dos' is 'backpack', which is a common item used to carry books, supplies, and personal belongings."
            },
            {
                "id": "0034_6",
                "type": "vrai-faux",
                "question": "'The English word for 'bureau' is 'desk'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'bureau' is indeed 'desk', which is a common piece of furniture used for writing, working, or studying."
            },
            {
                "id": "0034_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'règle'?",
                "options": ["Ruler", "Tape measure", "Measuring stick", "All of the above"],
                "correct_option": "Ruler",
                "explanation": "The English word for 'règle' is 'ruler', which is a common item used to measure length or draw straight lines."
            },
            {
                "id": "0034_8",
                "type": "vrai-faux",
                "question": "'The English word for 'chaise' is 'chair'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'chaise' is indeed 'chair', which is a common piece of furniture used for sitting."
            },
        ]
    ),
    (
        "0035",
        'Vocabulaire de base : vêtements, couleurs, nombres',
        "Anglais",
        "6ème",
        [
            {
                "id": "0035_1",
                "type": "qcm",
                "question": "What is the English word for 'chemise'?",
                "options": ["Shirt", "Pants", "Dress", "Skirt"],
                "correct_option": "Shirt",
                "explanation": "The English word for 'chemise' is 'shirt', which is a common piece of clothing worn on the upper body."
            },
            {
                "id": "0035_2",
                "type": "vrai-faux",
                "question": "'The English word for 'pantalon' is 'pants'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'pantalon' is indeed 'pants', which refers to a common piece of clothing worn on the lower body."
            },
            {
                "id": "0035_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'couleurs'?",
                "options": ["Colors", "Hues", "Shades", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Colors', 'Hues', and 'Shades' are all common English words that can be used to refer to different aspects of color and its variations."
            },
            {
                "id": "0035_4",
                "type": "vrai-faux",
                "question": "'The English word for 'nombres' is 'numbers'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'nombres' is indeed 'numbers', which refers to mathematical symbols used to represent quantities and perform calculations."
            },
            {
                "id": "0035_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'robe'?",
                "options": ["Dress", "Skirt", "Gown", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Dress', 'Skirt', and 'Gown' are all common English words that can be used to refer to different types of clothing worn on the lower body, especially for formal or semi-formal occasions."
            },
            {
                "id": "0035_6",
                "type": "vrai-faux",
                "question": "'The English word for 'chapeau' is 'hat'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'chapeau' is indeed 'hat', which is a common accessory worn on the head for protection from the sun, cold, or as a fashion statement."
            },
            {
                "id": "0035_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'couleurs'?",
                "options": ["Colors", "Tints", "Tones", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Colors', 'Tints', and 'Tones' are all common English words that can be used to refer to different aspects of color and its variations."
            },
            {
                "id": "0035_8",
                "type": "vrai-faux",
                "question": "'The English word for 'nombres' is 'digits'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'nombres' can indeed be translated as 'digits', which refers to the individual symbols used to represent numbers in the decimal system (0-9)."
            },
        ]
    ),
    (
        "0036",
        'Vocabulaire de base : couleurs, nombres, formes',
        "Anglais",
        "6ème",
        [
            {
                "id": "0036_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'couleurs'?",
                "options": ["Colors", "Hues", "Shades", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Colors', 'Hues', and 'Shades' are all common English words that can be used to refer to different aspects of color and its variations."
            },
            {
                "id": "0036_2",
                "type": "vrai-faux",
                "question": "'The English word for 'nombres' is 'numbers'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'nombres' is indeed 'numbers', which refers to mathematical symbols used to represent quantities and perform calculations."
            },
            {
                "id": "0036_3",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'formes'?",
                "options": ["Shapes", "Figures", "Geometric forms", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
            },
            {
                "id": "0036_4",
                "type": "vrai-faux",
                "question": "'The English word for 'couleurs' is 'colors'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'couleurs' is indeed 'colors', which refers to the visual perception of different wavelengths of light."
            },
            {
                "id": "0036_5",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'nombres'?",
                "options": ["Numbers", "Digits", "Numerals", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Numbers', 'Digits', and 'Numerals' are all common English words that can be used to refer to mathematical symbols used to represent quantities and perform calculations."
            },
            {
                "id": "0036_6",
                "type": "vrai-faux",
                "question": "'The English word for 'formes' is 'shapes'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
            },
            {
                "id": "0036_7",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'couleurs'?",
                "options": ["Colors", "Tints", "Tones", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Colors', 'Tints', and 'Tones' are all common English words that can be used to refer to different aspects of color and its variations."
            },
            {
                "id": "0036_8",
                "type": "vrai-faux",
                "question": "'The English word for 'nombres' is 'digits'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'nombres' can indeed be translated as 'digits', which refers to the individual symbols used to represent numbers in the decimal system (0-9)."
            },
        ]
    ),
    (
        "0037",
        'Vocabulaire de base : formes et prépositions de lieu',
        "Anglais",
        "6ème",
        [
            {
                "id": "0037_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'formes'?",
                "options": ["Shapes", "Figures", "Geometric forms", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
            },
            {
                "id": "0037_2",
                "type": "vrai-faux",
                "question": "'The English word for 'formes' is 'shapes'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
            },
            {
                "id": "0037_3",
                "type": "qcm",
                "question": "Which of the following is a common English preposition used to indicate location?",
                "options": ["In", "On", "At", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In', 'On', and 'At' are all common English prepositions that can be used to indicate location in different contexts. 'In' is used for enclosed spaces, 'On' is used for surfaces, and 'At' is used for specific points or locations."
            },
            {
                "id": "0037_4",
                "type": "vrai-faux",
                "question": "'The preposition 'in' is used to indicate location inside something' is a true statement.",
                "correct": True,
                "explanation": "The preposition 'in' is indeed used to indicate location inside something, such as 'The book is in the bag.'"
            },
            {
                "id": "0037_5",
                "type": "qcm",
                "question": "Which of the following is a common English preposition used to indicate location on a surface?",
                "options": ["In", "On", "At", "All of the above"],
                "correct_option": "On",
                "explanation": "'On' is a common English preposition used to indicate location on a surface, such as 'The book is on the table.'"
            },
            {
                "id": "0037_6",
                "type": "vrai-faux",
                "question": "'The preposition 'at' is used to indicate location at a specific point' is a true statement.",
                "correct": True,
                "explanation": "The preposition 'at' is indeed used to indicate location at a specific point, such as 'I will meet you at the park.'"
            },
            {
                "id": "0037_7",
                "type": "qcm",
                "question": "Which of the following is a common English preposition used to indicate location in relation to something else?",
                "options": ["In", "On", "At", "Next to"],
                "correct_option": "Next to",
                "explanation": "'Next to' is a common English preposition used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
            },
            {
                "id": "0037_8",
                "type": "vrai-faux",
                "question": "'The preposition 'next to' is used to indicate location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "The preposition 'next to' is indeed used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
            },
        ]
    ),
    (
        "0038",
        'Vocabulaire de base : formes et prépositions de lieu',
        "Anglais",
        "6ème",
        [
            {
                "id": "0038_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'formes'?",
                "options": ["Shapes", "Figures", "Geometric forms", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
            },
            {
                "id": "0038_2",
                "type": "vrai-faux",
                "question": "'The English word for 'formes' is 'shapes'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
            },
            {
                "id": "0038_3",
                "type": "qcm",
                "question": "Which of the following is a common English preposition used to indicate location?",
                "options": ["In", "On", "At", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In', 'On', and 'At' are all common English prepositions that can be used to indicate location in different contexts. 'In' is used for enclosed spaces, 'On' is used for surfaces, and 'At' is used for specific points or locations."
            },
            {
                "id": "0038_4",
                "type": "vrai-faux",
                "question": "'The preposition 'in' is used to indicate location inside something' is a true statement.",
                "correct": True,
                "explanation": "The preposition 'in' is indeed used to indicate location inside something, such as 'The book is in the bag.'"
            },
            {
                "id": "0038_5",
                "type": "qcm",
                "question": "Which of the following is a common English preposition used to indicate location on a surface?",
                "options": ["In", "On", "At", "All of the above"],
                "correct_option": "On",
                "explanation": "'On' is a common English preposition used to indicate location on a surface, such as 'The book is on the table.'"
            },
            {
                "id": "0038_6",
                "type": "vrai-faux",
                "question": "'The preposition 'at' is used to indicate location at a specific point' is a true statement.",
                "correct": True,
                "explanation": "The preposition 'at' is indeed used to indicate location at a specific point, such as 'I will meet you at the park.'"
            },
            {
                "id": "0038_7",
                "type": "qcm",
                "question": "Which of the following is a common English preposition used to indicate location in relation to something else?",
                "options": ["In", "On", "At", "Next to"],
                "correct_option": "Next to",
                "explanation": "'Next to' is a common English preposition used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
            },
            {
                "id": "0038_8",
                "type": "vrai-faux",
                "question": "'The preposition 'next to' is used to indicate location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "The preposition 'next to' is indeed used to indicate location in relation to something else, such as 'The book is next to the lamp.'"
            },
        ]
    ),
    (
        "0039",
        'Vocabulaire de base : formes et description de lieu',
        "Anglais",
        "6ème",
        [
            {
                "id": "0039_1",
                "type": "qcm",
                "question": "Which of the following is a common English word for 'formes'?",
                "options": ["Shapes", "Figures", "Geometric forms", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Shapes', 'Figures', and 'Geometric forms' are all common English words that can be used to refer to different types of shapes and forms in geometry and everyday life."
            },
            {
                "id": "0039_2",
                "type": "vrai-faux",
                "question": "'The English word for 'formes' is 'shapes'' is a true statement.",
                "correct": True,
                "explanation": "The English word for 'formes' is indeed 'shapes', which refers to the external form or appearance of an object or figure."
            },
            {
                "id": "0039_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0039_4",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
            {
                "id": "0039_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location behind something?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "Behind",
                "explanation": "'Behind' is a common English expression used to describe location behind something, such as 'The tree is behind the house.'"
            },
            {
                "id": "0039_6",
                "type": "vrai-faux",
                "question": "'The expression 'next to' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'Next to' is indeed an expression used to describe location in relation to something else, such as 'The book is next to the lamp.'"
            },
            {
                "id": "0039_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location in relation to something else?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0039_8",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
        ]
    ),
    (
        "0040",
        'Vocabulaire de base : description de lieu',
        "Anglais",
        "6ème",
        [
            {
                "id": "0040_1",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0040_2",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
            {
                "id": "0040_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location behind something?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "Behind",
                "explanation": "'Behind' is a common English expression used to describe location behind something, such as 'The tree is behind the house.'"
            },
            {
                "id": "0040_4",
                "type": "vrai-faux",
                "question": "'The expression 'next to' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'Next to' is indeed an expression used to describe location in relation to something else, such as 'The book is next to the lamp.'"
            },
            {
                "id": "0040_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location in relation to something else?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0040_6",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
            {
                "id": "0040_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location in relation to something else?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0040_8",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
        ]
    ),
    (
        "0041",
        'Vocabulaire de base : Description d\'une personne ou d\'un lieu',
        'Anglais',
        '6ème',
        [
            {
                "id": "0041_1",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location in relation to something else?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0041_2",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
            {
                "id": "0041_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location behind something?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "Behind",
                "explanation": "'Behind' is a common English expression used to describe location behind something, such as 'The tree is behind the house.'"
            },
            {
                "id": "0041_4",
                "type": "vrai-faux",
                "question": "'The expression 'next to' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'Next to' is indeed an expression used to describe location in relation to something else, such as 'The book is next to the lamp.'"
            },
            {
                "id": "0041_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location in relation to something else?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0041_6",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
            {
                "id": "0041_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe location in relation to something else?",
                "options": ["In front of", "Behind", "Next to", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'In front of', 'Behind', and 'Next to' are all common English expressions used to describe location in relation to other objects or landmarks."
            },
            {
                "id": "0041_8",
                "type": "vrai-faux",
                "question": "'The expression 'in front of' is used to describe location in relation to something else' is a true statement.",
                "correct": True,
                "explanation": "'In front of' is indeed an expression used to describe location in relation to something else, such as 'The car is parked in front of the house.'"
            },
        ]
    ),
    (
        "0042",
        'Vocabulaire de base : prononciation de chiffres et de lettres',
        'Anglais',
        '6ème',
        [
            {
                "id": "0042_1",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the number '1'?",
                "options": ["One", "Two", "Three", "Four"],
                "correct_option": "One",
                "explanation": "The correct English pronunciation for the number '1' is 'One'."
            },
            {
                "id": "0042_2",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the letter 'A' is 'ay'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the letter 'A' is indeed 'ay', which is a common way to pronounce this letter in English."
            },
            {
                "id": "0042_3",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the number '5'?",
                "options": ["Five", "Six", "Seven", "Eight"],
                "correct_option": "Five",
                "explanation": "The correct English pronunciation for the number '5' is 'Five'."
            },
            {
                "id": "0042_4",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the letter 'B' is 'bee'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the letter 'B' is indeed 'bee', which is a common way to pronounce this letter in English."
            },
            {
                "id": "0042_5",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the number '10'?",
                "options": ["Ten", "Eleven", "Twelve", "Thirteen"],
                "correct_option": "Ten",
                "explanation": "The correct English pronunciation for the number '10' is 'Ten'."
            },
            {
                "id": "0042_6",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the letter 'C' is 'see'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the letter 'C' is indeed 'see', which is a common way to pronounce this letter in English."
            },
            {
                "id": "0042_7",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the number '20'?",
                "options": ["Twenty", "Thirty", "Forty", "Fifty"],
                "correct_option": "Twenty",
                "explanation": "The correct English pronunciation for the number '20' is 'Twenty'."
            },
            {
                "id": "0042_8",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the letter 'D' is 'dee'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the letter 'D' is indeed 'dee', which is a common way to pronounce this letter in English."
            },
        ]
    ),
    (
        "0043",
        'Vocabulaire de base : prononciation de mots courants',
        'Anglais',
        '6ème',
        [
            {
                "id": "0043_1",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the word 'cat'?",
                "options": ["Cat", "Cot", "Cut", "Cot"],
                "correct_option": "Cat",
                "explanation": "The correct English pronunciation for the word 'cat' is 'Cat'."
            },
            {
                "id": "0043_2",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the word 'dog' is 'dog'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the word 'dog' is indeed 'dog', which is a common way to pronounce this word in English."
            },
            {
                "id": "0043_3",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the word 'house'?",
                "options": ["House", "Hose", "Hous", "Hous"],
                "correct_option": "House",
                "explanation": "The correct English pronunciation for the word 'house' is 'House'."
            },
            {
                "id": "0043_4",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the word 'car' is 'car'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the word 'car' is indeed 'car', which is a common way to pronounce this word in English."
            },
            {
                "id": "0043_5",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the word 'tree'?",
                "options": ["Tree", "Trey", "Tee", "Trey"],
                "correct_option": "Tree",
                "explanation": "The correct English pronunciation for the word 'tree' is 'Tree'."
            },
            {
                "id": "0043_6",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the word 'book' is 'book'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the word 'book' is indeed 'book', which is a common way to pronounce this word in English."
            },
            {
                "id": "0043_7",
                "type": "qcm",
                "question": "Which of the following is the correct English pronunciation for the word 'water'?",
                "options": ["Water", "Watter", "Woter", "Watter"],
                "correct_option": "Water",
                "explanation": "The correct English pronunciation for the word 'water' is 'Water'."
            },
            {
                "id": "0043_8",
                "type": "vrai-faux",
                "question": "'The English pronunciation for the word 'food' is 'food'' is a true statement.",
                "correct": True,
                "explanation": "The English pronunciation for the word 'food' is indeed 'food', which is a common way to pronounce this word in English."
            },
        ]
    ),
    (
        "0044",
        'Vocabulaire de base : phrases courantes',
        'Anglais',
        '6ème',
        [
            {
                "id": "0044_1",
                "type": "qcm",
                "question": "Which of the following is a common English greeting?",
                "options": ["Hello", "Hi", "Hey", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Hello', 'Hi', and 'Hey' are all common English greetings that can be used in different contexts to greet someone."
            },
            {
                "id": "0044_2",
                "type": "vrai-faux",
                "question": "'The English phrase for 'Comment ça va?' is 'How are you?'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'Comment ça va?' is indeed 'How are you?', which is a common way to ask someone about their well-being in English."
            },
            {
                "id": "0044_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to say goodbye?",
                "options": ["Goodbye", "Bye", "See you later", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Goodbye', 'Bye', and 'See you later' are all common English expressions used to say goodbye in different contexts."
            },
            {
                "id": "0044_4",
                "type": "vrai-faux",
                "question": "'The English phrase for 'Merci' is 'Thank you'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'Merci' is indeed 'Thank you', which is a common way to express gratitude in English."
            },
            {
                "id": "0044_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to apologize?",
                "options": ["Sorry", "I apologize", "My apologies", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Sorry', 'I apologize', and 'My apologies' are all common English expressions used to apologize in different contexts."
            },
            {
                "id": "0044_6",
                "type": "vrai-faux",
                "question": "The English phrase for 'S'il vous plaît' is 'Please'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'S'il vous plaît' is indeed 'Please', which is a common way to make a polite request in English."
            },
            {
                "id": "0044_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to express gratitude?",
                "options": ["Thank you", "Thanks", "I appreciate it", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Thank you', 'Thanks', and 'I appreciate it' are all common English expressions used to express gratitude in different contexts."
            },
            {
                "id": "0044_8",
                "type": "vrai-faux",
                "question": "'The English phrase for 'Excusez-moi' is 'Excuse me'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'Excusez-moi' is indeed 'Excuse me', which is a common way to get someone's attention or to apologize for an interruption in English."
            },
        ]
    ),
    (
        "0045",
        'Vocabulaire de base : phrases courantes pour dire bonjour, au revoir, merci, s\'il vous plaît, etc.',
        'Anglais',
        '6ème',
        [
            {
                "id": "0045_1",
                "type": "qcm",
                "question": "Which of the following is a common English greeting?",
                "options": ["Hello", "Hi", "Hey", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Hello', 'Hi', and 'Hey' are all common English greetings that can be used in different contexts to greet someone."
            },
            {
                "id": "0045_2",
                "type": "vrai-faux",
                "question": "'The English phrase for 'Comment ça va?' is 'How are you?'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'Comment ça va?' is indeed 'How are you?', which is a common way to ask someone about their well-being in English."
            },
            {
                "id": "0045_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to say goodbye?",
                "options": ["Goodbye", "Bye", "See you later", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Goodbye', 'Bye', and 'See you later' are all common English expressions used to say goodbye in different contexts."
            },
            {
                "id": "0045_4",
                "type": "vrai-faux",
                "question": "'The English phrase for 'Merci' is 'Thank you'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'Merci' is indeed 'Thank you', which is a common way to express gratitude in English."
            },
            {
                "id": "0045_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to apologize?",
                "options": ["Sorry", "I apologize", "My apologies", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Sorry', 'I apologize', and 'My apologies' are all common English expressions used to apologize in different contexts."
            },
            {
                "id": "0045_6",
                "type": "vrai-faux",
                "question": "'The English phrase for 'S'il vous plaît' is 'Please'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'S'il vous plaît' is indeed 'Please', which is a common way to make a polite request in English."
            },
            {
                "id": "0045_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to express gratitude?",
                "options": ["Thank you", "Thanks", "I appreciate it", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'Thank you', 'Thanks', and 'I appreciate it' are all common English expressions used to express gratitude in different contexts."
            },
            {
                "id": "0045_8",
                "type": "vrai-faux",
                "question": "'The English phrase for 'Excusez-moi' is 'Excuse me'' is a true statement.",
                "correct": True,
                "explanation": "The English phrase for 'Excusez-moi' is indeed 'Excuse me', which is a common way to get someone's attention or to apologize for an interruption in English."
            },
        ]
    ),
    (
        "0046",
        'Vocabulaire de base : phrases courantes pour parler de l\'extérieur',
        'Anglais',
        '6ème',
        [
            {
                "id": "0046_1",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the weather outside?",
                "options": ["It's sunny", "It's raining", "It's windy", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'It's sunny', 'It's raining', and 'It's windy' are all common English expressions used to describe the weather outside."
            },
            {
                "id": "0046_2",
                "type": "vrai-faux",
                "question": "'The English expression for 'Il fait beau' is 'It's sunny'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'Il fait beau' is indeed 'It's sunny', which is a common way to describe nice weather in English."
            },
            {
                "id": "0046_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the weather outside when it's raining?",
                "options": ["It's sunny", "It's raining", "It's windy", "All of the above"],
                "correct_option": "It's raining",
                "explanation": "'It's raining' is a common English expression used to describe the weather outside when it's raining."
            },
            {
                "id": "0046_4",
                "type": "vrai-faux",
                "question": "The English expression for 'Il pleut' is 'It's raining'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'Il pleut' is indeed 'It's raining', which is a common way to describe rainy weather in English."
            },
            {
                "id": "0046_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the weather outside when it's windy?",
                "options": ["It's sunny", "It's raining", "It's windy", "All of the above"],
                "correct_option": "It's windy",
                "explanation": "'It's windy' is a common English expression used to describe the weather outside when it's windy."
            },
            {
                "id": "0046_6",
                "type": "vrai-faux",
                "question": "The English expression for 'Il fait du vent' is 'It's windy'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'Il fait du vent' is indeed 'It's windy', which is a common way to describe windy weather in English."
            },
            {
                "id": "0046_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the weather outside when it's cold?",
                "options": ["It's sunny", "It's raining", "It's cold", "All of the above"],
                "correct_option": "It's cold",
                "explanation": "'It's cold' is a common English expression used to describe the weather outside when it's cold."
            },
            {
                "id": "0046_8",
                "type": "vrai-faux",
                "question": "The English expression for 'Il fait froid' is 'It's cold'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'Il fait froid' is indeed 'It's cold', which is a common way to describe cold weather in English."
            },
        ]
    ),
    (
        "0047",
        'Vocabulaire de base : phrases courantes pour parler de l\'intérieur',
        'Anglais',
        '6ème',
        [
            {
                "id": "0047_1",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the interior of a house?",
                "options": ["It's cozy", "It's spacious", "It's modern", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'It's cozy', 'It's spacious', and 'It's modern' are all common English expressions used to describe the interior of a house."
            },
            {
                "id": "0047_2",
                "type": "vrai-faux",
                "question": "The English expression for 'C'est confortable' is 'It's cozy'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est confortable' is indeed 'It's cozy', which is a common way to describe a comfortable interior in English."
            },
            {
                "id": "0047_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the interior of a house when it's spacious?",
                "options": ["It's cozy", "It's spacious", "It's modern", "All of the above"],
                "correct_option": "It's spacious",
                "explanation": "'It's spacious' is a common English expression used to describe the interior of a house when it's spacious."
            },
            {
                "id": "0047_4",
                "type": "vrai-faux",
                "question": "The English expression for 'C'est spacieux' is 'It's spacious'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est spacieux' is indeed 'It's spacious', which is a common way to describe a spacious interior in English."
            },
            {
                "id": "0047_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the interior of a house when it's modern?",
                "options": ["It's cozy", "It's spacious", "It's modern", "All of the above"],
                "correct_option": "It's modern",
                "explanation": "'It's modern' is a common English expression used to describe the interior of a house when it's modern."
            },
            {
                "id": "0047_6",
                "type": "vrai-faux",
                "question": "The English expression for 'C'est moderne' is 'It's modern'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est moderne' is indeed 'It's modern', which is a common way to describe a modern interior in English."
            },
            {
                "id": "0047_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe the interior of a house when it's old-fashioned?",
                "options": ["It's cozy", "It's spacious", "It's old-fashioned", "All of the above"],
                "correct_option": "It's old-fashioned",
                "explanation": "'It's old-fashioned' is a common English expression used to describe the interior of a house when it's old-fashioned."
            },
            {
                "id": "0047_8",
                "type": "vrai-faux",
                "question": "'The English expression for 'C'est démodé' is 'It's old-fashioned'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est démodé' is indeed 'It's old-fashioned', which is a common way to describe an old-fashioned interior in English."
            },
        ]
    ),
    (
        "0048",
        'Vocabulaire de base : phrases courantes pour parler de la voiture',
        'Anglais',
        '6ème',
        [
            {
                "id": "0048_1",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe a car?",
                "options": ["It's fast", "It's slow", "It's new", "All of the above"],
                "correct_option": "All of the above",
                "explanation": "'It's fast', 'It's slow', and 'It's new' are all common English expressions used to describe a car in different contexts."
            },
            {
                "id": "0048_2",
                "type": "vrai-faux",
                "question": "'The English expression for 'C'est rapide' is 'It's fast'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est rapide' is indeed 'It's fast', which is a common way to describe a fast car in English."
            },
            {
                "id": "0048_3",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe a car when it's slow?",
                "options": ["It's fast", "It's slow", "It's new", "All of the above"],
                "correct_option": "It's slow",
                "explanation": "'It's slow' is a common English expression used to describe a car when it's slow."
            },
            {
                "id": "0048_4",
                "type": "vrai-faux",
                "question": "'The English expression for 'C'est lent' is 'It's slow'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est lent' is indeed 'It's slow', which is a common way to describe a slow car in English."
            },
            {
                "id": "0048_5",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe a car when it's new?",
                "options": ["It's fast", "It's slow", "It's new", "All of the above"],
                "correct_option": "It's new",
                "explanation": "'It's new' is a common English expression used to describe a car when it's new."
            },
            {
                "id": "0048_6",
                "type": "vrai-faux",
                "question": "'The English expression for 'C'est neuf' is 'It's new'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est neuf' is indeed 'It's new', which is a common way to describe a new car in English."
            },
            {
                "id": "0048_7",
                "type": "qcm",
                "question": "Which of the following is a common English expression used to describe a car when it's old?",
                "options": ["It's fast", "It's slow", "It's old", "All of the above"],
                "correct_option": "It's old",
                "explanation": "'It's old' is a common English expression used to describe a car when it's old."
            },
            {
                "id": "0048_8",
                "type": "vrai-faux",
                "question": "'The English expression for 'C'est vieux' is 'It's old'' is a true statement.",
                "correct": True,
                "explanation": "The English expression for 'C'est vieux' is indeed 'It's old', which is a common way to describe an old car in English."
            },
        ]
    )
]
def make_quiz(qid, title, subject, level, questions):
        created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
        runtime_questions = []
        for question in questions:
            qtype = str(question.get("type", "texte"))
            if qtype == "qcm":
                runtime_questions.append({"type": "qcm", "question": str(question.get("question", "")), "choices": list(question.get("options", []))})
            elif qtype == "vrai-faux":
                runtime_questions.append({"type": "vrai-faux", "question": str(question.get("question", ""))})
            else:
                runtime_questions.append({"type": "open", "question": str(question.get("question", ""))})
        return {
            "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at},
            "quiz": {"title": title, "type": "quiz", "level": level, "subject": subject, "question_count": len(runtime_questions), "passing_score": 70, "time_limit_minutes": 15, "questions": runtime_questions},
            "exercisenotion": [],
            "exerciseresponses": [],
        }

def make_answers(qid, title, subject, level, questions):
        answers = []
        for index, q in enumerate(questions):
            if q["type"] == "qcm":
                answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": q["correct_option"], "correction": q["explanation"]})
            elif q["type"] == "vrai-faux":
                answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q["correct"] else "faux", "correction": q["explanation"]})
            else:
                answers.append({"index": index, "question_id": index + 1, "type": "open", "answer": q["correct_answer"], "correction": q["explanation"]})
        return {
            "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "level": level, "subject": subject},
            "quiz": {"title": title, "question_count": len(answers), "level": level, "subject": subject, "answers": answers},
        }

def write_quiz_files():
        os.makedirs(QUIZ_DIR, exist_ok=True)
        os.makedirs(ANSWERS_DIR, exist_ok=True)
        for qid, title, subject, level, questions in quizzes_data:
            quiz = make_quiz(qid, title, subject, level, questions)
            answers = make_answers(qid, title, subject, level, questions)
            with open(os.path.join(QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as f:
                json.dump(quiz, f, ensure_ascii=False, indent=2)
                f.write("\n")
            with open(os.path.join(ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as f:
                json.dump(answers, f, ensure_ascii=False, indent=2)
                f.write("\n")
        print(f"{len(quizzes_data)} quiz generated in {OUTPUT_DIR}")

if __name__ == "__main__":
        write_quiz_files()
