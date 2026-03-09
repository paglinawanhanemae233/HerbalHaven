-- Herbal Haven Database Setup
-- Run this script in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS herbal_haven CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE herbal_haven;

-- Table: herbs
CREATE TABLE IF NOT EXISTS herbs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    scientific_name VARCHAR(150),
    description TEXT,
    image_url VARCHAR(255),
    preparation_methods TEXT,
    dosage_info TEXT,
    safety_warnings TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: health_conditions
CREATE TABLE IF NOT EXISTS health_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    condition_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    INDEX idx_condition (condition_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: herbs_conditions (linking table)
CREATE TABLE IF NOT EXISTS herbs_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    herb_id INT NOT NULL,
    condition_id INT NOT NULL,
    effectiveness_note TEXT,
    FOREIGN KEY (herb_id) REFERENCES herbs(id) ON DELETE CASCADE,
    FOREIGN KEY (condition_id) REFERENCES health_conditions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_herb_condition (herb_id, condition_id),
    INDEX idx_herb (herb_id),
    INDEX idx_condition (condition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: contraindications
CREATE TABLE IF NOT EXISTS contraindications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    herb_id INT NOT NULL,
    warning_text TEXT NOT NULL,
    severity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    category VARCHAR(50),
    FOREIGN KEY (herb_id) REFERENCES herbs(id) ON DELETE CASCADE,
    INDEX idx_herb (herb_id),
    INDEX idx_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: chat_history
CREATE TABLE IF NOT EXISTS chat_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) NOT NULL,
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_sessions
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    user_info JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Health Conditions
INSERT INTO health_conditions (condition_name, description) VALUES
('Stress', 'General stress and anxiety'),
('Sleep Issues', 'Insomnia and sleep disorders'),
('Digestion', 'Digestive problems and indigestion'),
('Headache', 'Headaches and migraines'),
('Nausea', 'Nausea and motion sickness'),
('Inflammation', 'Inflammatory conditions'),
('Joint Pain', 'Arthritis and joint discomfort'),
('Anxiety', 'Anxiety disorders and nervousness'),
('Immune Support', 'Boosting immune system'),
('Cardiovascular', 'Heart and circulatory health'),
('Skin Issues', 'Skin conditions and wounds'),
('Energy', 'Fatigue and low energy'),
('Blood Sugar', 'Blood sugar regulation'),
('Cold/Flu', 'Common cold and flu symptoms'),
('Liver Health', 'Liver function and detoxification');

-- Insert Herbs
INSERT INTO herbs (name, scientific_name, description, image_url, preparation_methods, dosage_info, safety_warnings) VALUES
('Chamomile', 'Matricaria chamomilla', 'Chamomile is a gentle herb known for its calming and soothing properties. It has been used for centuries to promote relaxation, improve sleep quality, and support digestive health.', '/images/herbs/chamomile.jpg', 'Tea: Steep 1-2 teaspoons of dried flowers in hot water for 5-10 minutes. Tincture: 1-4 ml three times daily. Topical: Use as a compress for skin irritation.', 'Tea: 1-4 cups daily. Tincture: 1-4 ml (20-80 drops) three times daily. Safe for most adults when used appropriately.', 'May cause allergic reactions in people sensitive to ragweed. Not recommended during pregnancy without medical supervision. May interact with blood-thinning medications.'),
('Peppermint', 'Mentha piperita', 'Peppermint is a refreshing herb with powerful digestive benefits. It helps relieve indigestion, bloating, and can soothe headaches when applied topically.', '/images/herbs/peppermint.jpg', 'Tea: Steep 1 teaspoon of dried leaves in hot water for 5-7 minutes. Essential oil: Dilute and apply topically for headaches. Capsules: Follow product instructions.', 'Tea: 1-3 cups daily between meals. Essential oil: Use sparingly, always diluted. Capsules: Typically 0.2-0.4 ml of enteric-coated capsules.', 'May cause heartburn in some individuals. Not recommended for infants or young children. Essential oil should never be ingested undiluted.'),
('Ginger', 'Zingiber officinale', 'Ginger is a warming spice with anti-inflammatory and digestive properties. It is particularly effective for nausea, motion sickness, and inflammatory conditions.', '/images/herbs/ginger.jpg', 'Tea: Simmer 1-2 slices of fresh ginger root in water for 10-15 minutes. Fresh: Chew small pieces. Powder: Add to food or make tea. Capsules: Follow product instructions.', 'Fresh: 1-2 grams daily. Tea: 2-4 cups daily. Powder: 1-2 grams. Capsules: 500-1000 mg up to 4 times daily for nausea.', 'May interact with blood-thinning medications. Use cautiously if you have gallstones. High doses may cause heartburn. Not recommended during pregnancy without medical advice.'),
('Turmeric', 'Curcuma longa', 'Turmeric contains curcumin, a powerful anti-inflammatory compound. It is used for joint pain, inflammation, and overall wellness support.', '/images/herbs/turmeric.jpg', 'Tea: Simmer 1 teaspoon of powder in water with black pepper for 10 minutes. Golden milk: Mix with warm milk and honey. Capsules: Follow product instructions. Add to food.', 'Powder: 1-3 grams daily. Curcumin supplements: 500-1000 mg daily. Best absorbed with black pepper and fat.', 'May interact with blood-thinning medications and diabetes medications. May increase risk of kidney stones in susceptible individuals. Use cautiously if you have gallbladder issues.'),
('Lavender', 'Lavandula angustifolia', 'Lavender is renowned for its calming and relaxing effects. It helps reduce anxiety, improve sleep, and can be used topically for skin conditions.', '/images/herbs/lavender.jpg', 'Tea: Steep 1-2 teaspoons of dried flowers in hot water for 5-10 minutes. Essential oil: Use in aromatherapy or dilute for topical use. Pillow sachets: Place dried flowers in a sachet.', 'Tea: 1-2 cups daily, especially before bedtime. Essential oil: 2-4 drops in diffuser or diluted for topical use. Safe for most adults.', 'Essential oil should not be ingested. May cause skin irritation if not properly diluted. Use cautiously during pregnancy.'),
('Echinacea', 'Echinacea purpurea', 'Echinacea is a popular immune-supporting herb, commonly used to help prevent and shorten the duration of colds and flu.', '/images/herbs/echinacea.jpg', 'Tea: Steep 1-2 teaspoons of dried root or leaves in hot water for 10-15 minutes. Tincture: 2-4 ml three times daily. Capsules: Follow product instructions.', 'Tea: 2-4 cups daily at first sign of symptoms. Tincture: 2-4 ml (40-80 drops) three times daily. Capsules: 300-500 mg three times daily. Use for short periods (1-2 weeks).', 'Not recommended for people with autoimmune disorders. May interact with immunosuppressant medications. Not for long-term use. Avoid if allergic to ragweed.'),
('Garlic', 'Allium sativum', 'Garlic is a powerful herb with cardiovascular and immune benefits. It has antimicrobial properties and supports heart health.', '/images/herbs/garlic.jpg', 'Fresh: Crush or chop and let sit 10 minutes before consuming. Cooked: Add to meals. Supplements: Aged garlic extract or capsules. Oil: Use in cooking.', 'Fresh: 1-2 cloves daily. Supplements: 600-1200 mg daily of aged garlic extract. Best consumed raw or lightly cooked for maximum benefits.', 'May increase bleeding risk, especially with blood-thinning medications. May cause digestive upset in some people. Strong odor may be unpleasant.'),
('Aloe Vera', 'Aloe barbadensis', 'Aloe vera is best known for its skin-healing properties, but it also supports digestive health when taken internally.', '/images/herbs/aloe-vera.jpg', 'Topical: Apply fresh gel directly to skin. Internal: Use processed aloe vera juice (not raw latex). Gel: Extract from fresh leaves.', 'Topical: Apply as needed to affected areas. Internal juice: 30-60 ml daily (use processed, not raw latex).', 'Internal use of raw aloe latex can be dangerous. Only use processed aloe products for internal use. May cause digestive upset. Not recommended during pregnancy.'),
('Green Tea', 'Camellia sinensis', 'Green tea is rich in antioxidants and provides gentle energy support. It supports cardiovascular health and overall wellness.', '/images/herbs/green-tea.jpg', 'Tea: Steep 1 teaspoon of leaves in hot (not boiling) water for 2-3 minutes. Can be consumed hot or cold. Matcha: Whisk powder in water.', '2-3 cups daily. Contains caffeine (20-45 mg per cup), so monitor intake. Best consumed between meals.', 'Contains caffeine - may cause insomnia, anxiety, or jitteriness in sensitive individuals. May interact with certain medications. Limit during pregnancy.'),
('Valerian Root', 'Valeriana officinalis', 'Valerian is a powerful sleep aid and anxiety reducer. It has sedative properties that help promote restful sleep.', '/images/herbs/valerian.jpg', 'Tea: Steep 1-2 teaspoons of dried root in hot water for 10-15 minutes. Tincture: 1-3 ml before bedtime. Capsules: Follow product instructions.', 'Tea: 1 cup 30-60 minutes before bedtime. Tincture: 1-3 ml (20-60 drops) before bed. Capsules: 300-600 mg before sleep.', 'May cause drowsiness - do not drive or operate machinery. May interact with sedatives and alcohol. Not recommended for long-term daily use. Some people may experience opposite effects.'),
('Cinnamon', 'Cinnamomum verum', 'Cinnamon helps regulate blood sugar and has anti-inflammatory properties. It is warming and supports digestive health.', '/images/herbs/cinnamon.jpg', 'Tea: Steep 1 teaspoon of powder or a stick in hot water for 10 minutes. Add to food and beverages. Supplements: Follow product instructions.', 'Powder: 1-2 grams daily (about 1/2 to 1 teaspoon). Supplements: 500-1000 mg daily. Best consumed with meals.', 'High doses may cause liver damage. Use Ceylon cinnamon rather than cassia for regular consumption. May interact with diabetes medications.'),
('Holy Basil (Tulsi)', 'Ocimum tenuiflorum', 'Holy basil, or Tulsi, is an adaptogenic herb that helps the body manage stress. It supports immune function and overall resilience.', '/images/herbs/holy-basil.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 5-10 minutes. Tincture: 2-4 ml daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml (40-80 drops) daily. Capsules: 400-600 mg daily. Can be used long-term.', 'May lower blood sugar - monitor if diabetic. May slow blood clotting. Use cautiously before surgery. Generally safe for most adults.'),
('Ashwagandha', 'Withania somnifera', 'Ashwagandha is an adaptogenic herb that helps the body manage stress, supports energy levels, and promotes overall vitality.', '/images/herbs/ashwagandha.jpg', 'Powder: Mix 1/2 to 1 teaspoon in warm milk or water. Capsules: Follow product instructions. Tincture: 2-4 ml daily.', 'Powder: 1-3 grams daily. Capsules: 300-600 mg daily. Tincture: 2-4 ml daily. Best taken with food. Can be used long-term.', 'May interact with thyroid medications. May lower blood sugar. Use cautiously if you have autoimmune conditions. Not recommended during pregnancy.'),
('Dandelion', 'Taraxacum officinale', 'Dandelion supports liver health and digestion. It acts as a gentle diuretic and supports detoxification processes.', '/images/herbs/dandelion.jpg', 'Tea: Steep 1-2 teaspoons of dried root or leaves in hot water for 10-15 minutes. Fresh: Use young leaves in salads. Tincture: 2-4 ml daily.', 'Tea: 2-3 cups daily. Root tea: Best for liver support. Leaf tea: Best for gentle diuretic effect. Tincture: 2-4 ml daily.', 'May cause allergic reactions in people sensitive to ragweed. May interact with diuretic medications. Use cautiously if you have gallbladder issues.'),
('Elderberry', 'Sambucus nigra', 'Elderberry is highly effective for immune support, particularly for cold and flu symptoms. It has antiviral properties.', '/images/herbs/elderberry.jpg', 'Syrup: Take 1-2 tablespoons daily. Tea: Steep 1-2 teaspoons of dried berries in hot water. Capsules: Follow product instructions.', 'Syrup: 1-2 tablespoons (15-30 ml) daily for prevention, up to 4 times daily during illness. Tea: 2-3 cups daily. Best taken at first sign of symptoms.', 'Raw berries are toxic - only use cooked or processed products. May interact with immunosuppressant medications. Use cautiously if you have autoimmune conditions.'),
('Milk Thistle', 'Silybum marianum', 'Milk thistle is renowned for its liver-protective properties. It supports liver detoxification and helps protect liver cells from damage.', '/images/herbs/milk-thistle.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml three times daily. Tea: Steep 1-2 teaspoons of seeds in hot water for 10-15 minutes.', 'Capsules: 150-300 mg of silymarin extract, 2-3 times daily. Tincture: 2-4 ml (40-80 drops) three times daily. Tea: 2-3 cups daily.', 'Generally safe for most adults. May cause mild digestive upset. Use cautiously if you have ragweed allergies.'),
('St. John\'s Wort', 'Hypericum perforatum', 'St. John\'s Wort is commonly used for mild to moderate depression and mood support. It has been studied extensively for mental wellness.', '/images/herbs/st-johns-wort.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Tea: Steep 1-2 teaspoons of dried flowers in hot water.', 'Capsules: 300-600 mg standardized extract, 2-3 times daily. Tincture: 2-4 ml daily. Tea: 2-3 cups daily. Takes 4-6 weeks to see effects.', 'May interact with many medications including antidepressants, birth control, and blood thinners. Not recommended during pregnancy. May cause photosensitivity.'),
('Ginkgo Biloba', 'Ginkgo biloba', 'Ginkgo biloba is known for supporting cognitive function, memory, and circulation. It is one of the oldest living tree species.', '/images/herbs/ginkgo-biloba.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Extract: Standardized extract is most effective.', 'Capsules: 120-240 mg standardized extract daily, divided into 2-3 doses. Tincture: 2-4 ml daily. Takes several weeks to see benefits.', 'May increase bleeding risk. Avoid before surgery. May interact with blood-thinning medications. May cause headaches or digestive upset in some.'),
('Ginseng', 'Panax ginseng', 'Ginseng is an adaptogenic herb that supports energy, stamina, and overall vitality. It helps the body adapt to stress.', '/images/herbs/ginseng.jpg', 'Tea: Steep 1-2 grams of root slices in hot water for 10-15 minutes. Capsules: Follow product instructions. Tincture: 2-4 ml daily.', 'Tea: 1-2 cups daily. Capsules: 200-400 mg standardized extract daily. Tincture: 2-4 ml daily. Use in cycles (2-3 weeks on, 1-2 weeks off).', 'May increase blood pressure. May interact with blood-thinning medications and diabetes medications. Not recommended during pregnancy. May cause insomnia if taken late in day.'),
('Rhodiola', 'Rhodiola rosea', 'Rhodiola is an adaptogenic herb that helps combat fatigue and stress. It supports mental performance and physical endurance.', '/images/herbs/rhodiola.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Extract: Standardized extract is preferred.', 'Capsules: 200-400 mg standardized extract daily, taken in morning. Tincture: 2-4 ml daily. Best taken on empty stomach.', 'May cause jitteriness or insomnia if taken late in day. May interact with antidepressants. Generally safe for most adults.'),
('Licorice Root', 'Glycyrrhiza glabra', 'Licorice root soothes the digestive system and supports respiratory health. It has anti-inflammatory and expectorant properties.', '/images/herbs/licorice-root.jpg', 'Tea: Steep 1-2 teaspoons of root in hot water for 10-15 minutes. Tincture: 2-4 ml daily. DGL tablets: Chewable form for digestive support.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml daily. DGL: 300-400 mg, 3 times daily before meals. Limit use to 4-6 weeks.', 'May raise blood pressure with long-term use. Avoid if you have high blood pressure, heart disease, or kidney disease. DGL form is safer for extended use.'),
('Marshmallow Root', 'Althaea officinalis', 'Marshmallow root is a demulcent herb that soothes irritated tissues. It is excellent for digestive and respiratory comfort.', '/images/herbs/marshmallow-root.jpg', 'Tea: Cold infusion - steep 1-2 teaspoons in cold water overnight. Tincture: 2-4 ml three times daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml (40-80 drops) three times daily. Capsules: 500-1000 mg, 3 times daily.', 'May slow absorption of other medications - take 1 hour before or after other medications. Generally very safe.'),
('Slippery Elm', 'Ulmus rubra', 'Slippery elm is a soothing demulcent that coats and protects irritated tissues in the digestive and respiratory tracts.', '/images/herbs/slippery-elm.jpg', 'Powder: Mix 1-2 teaspoons in water or juice. Capsules: Follow product instructions. Lozenges: For throat irritation.', 'Powder: 1-2 teaspoons, 3-4 times daily mixed in liquid. Capsules: 400-500 mg, 3-4 times daily. Take with plenty of water.', 'May slow absorption of medications - take 1-2 hours before or after other medications. Generally very safe and gentle.'),
('Fennel', 'Foeniculum vulgare', 'Fennel supports digestive health and can help relieve gas, bloating, and indigestion. It has a pleasant licorice-like flavor.', '/images/herbs/fennel.jpg', 'Tea: Steep 1-2 teaspoons of seeds in hot water for 10 minutes. Chew seeds after meals. Essential oil: Dilute for topical use.', 'Tea: 1-3 cups daily, especially after meals. Seeds: Chew 1/2 to 1 teaspoon after meals. Essential oil: Use sparingly, always diluted.', 'Generally safe. May cause allergic reactions in people sensitive to celery or carrots. Use cautiously during pregnancy.'),
('Fenugreek', 'Trigonella foenum-graecum', 'Fenugreek supports blood sugar regulation and can help with digestive issues. It is also used to support lactation.', '/images/herbs/fenugreek.jpg', 'Tea: Steep 1-2 teaspoons of seeds in hot water. Capsules: Follow product instructions. Seeds: Can be sprouted or cooked.', 'Tea: 2-3 cups daily. Capsules: 500-1000 mg, 2-3 times daily. Seeds: 1-2 teaspoons daily. Best taken with meals.', 'May lower blood sugar - monitor if diabetic. May interact with diabetes medications. Not recommended during pregnancy. May cause maple syrup-like odor in urine.'),
('Burdock Root', 'Arctium lappa', 'Burdock root supports liver health and skin conditions. It acts as a gentle blood purifier and supports detoxification.', '/images/herbs/burdock-root.jpg', 'Tea: Simmer 1-2 teaspoons of dried root in water for 15-20 minutes. Tincture: 2-4 ml three times daily. Can be cooked as food.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml (40-80 drops) three times daily. Fresh root: Can be cooked and eaten as vegetable.', 'May cause allergic reactions in people sensitive to ragweed. May interact with diuretic medications. Generally safe when used appropriately.'),
('Nettle', 'Urtica dioica', 'Nettle is a nutrient-rich herb that supports overall health. It is excellent for allergies, inflammation, and as a general tonic.', '/images/herbs/nettle.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 10-15 minutes. Tincture: 2-4 ml three times daily. Capsules: Follow product instructions.', 'Tea: 2-4 cups daily. Tincture: 2-4 ml (40-80 drops) three times daily. Capsules: 300-600 mg, 2-3 times daily.', 'Fresh plant can cause skin irritation - only use dried or cooked. May interact with blood-thinning medications. Generally safe when processed.'),
('Red Clover', 'Trifolium pratense', 'Red clover is rich in isoflavones and supports women\'s health, particularly during menopause. It also supports skin health.', '/images/herbs/red-clover.jpg', 'Tea: Steep 1-2 teaspoons of dried flowers in hot water for 10-15 minutes. Tincture: 2-4 ml daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml daily. Capsules: 40-80 mg isoflavones daily. Best used consistently for several weeks.', 'May interact with blood-thinning medications. Not recommended during pregnancy or breastfeeding. May affect hormone-sensitive conditions.'),
('Saw Palmetto', 'Serenoa repens', 'Saw palmetto is primarily used to support prostate health in men. It may help with urinary symptoms related to prostate enlargement.', '/images/herbs/saw-palmetto.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Extract: Standardized extract is most effective.', 'Capsules: 320 mg standardized extract daily, divided into 2 doses. Tincture: 2-4 ml daily. Takes 4-6 weeks to see benefits.', 'May interact with hormone medications. May affect blood clotting. Generally safe for most men. Not recommended for women or children.'),
('Cranberry', 'Vaccinium macrocarpon', 'Cranberry is well-known for supporting urinary tract health. It helps prevent bacteria from adhering to urinary tract walls.', '/images/herbs/cranberry.jpg', 'Juice: Drink unsweetened cranberry juice. Capsules: Follow product instructions. Dried berries: Add to food or eat as snack.', 'Juice: 8-16 oz unsweetened juice daily. Capsules: 300-400 mg extract, 2-3 times daily. Dried: 1/4 to 1/2 cup daily.', 'May interact with blood-thinning medications. High sugar content in sweetened juices may be problematic. Generally safe.'),
('Uva Ursi', 'Arctostaphylos uva-ursi', 'Uva ursi is a traditional remedy for urinary tract infections. It has antimicrobial properties that support urinary health.', '/images/herbs/uva-ursi.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 10-15 minutes. Tincture: 2-4 ml three times daily.', 'Tea: 2-3 cups daily for short-term use (up to 1 week). Tincture: 2-4 ml (40-80 drops) three times daily. Not for long-term use.', 'Not for long-term use - limit to 1 week. Not recommended during pregnancy or breastfeeding. May cause stomach upset. Requires alkaline urine to be effective.'),
('Bilberry', 'Vaccinium myrtillus', 'Bilberry supports eye health and vision. It is rich in antioxidants and may help with night vision and eye strain.', '/images/herbs/bilberry.jpg', 'Capsules: Follow product instructions. Tea: Steep 1-2 teaspoons of dried berries in hot water. Fresh or frozen berries: Eat as food.', 'Capsules: 80-160 mg standardized extract, 2-3 times daily. Tea: 2-3 cups daily. Fresh: 1/2 to 1 cup daily.', 'May interact with blood-thinning medications. Generally safe. May lower blood sugar slightly.'),
('Hawthorn', 'Crataegus monogyna', 'Hawthorn is a cardiovascular tonic that supports heart health. It helps improve circulation and supports healthy blood pressure.', '/images/herbs/hawthorn.jpg', 'Tea: Steep 1-2 teaspoons of berries or flowers in hot water for 10-15 minutes. Tincture: 2-4 ml three times daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml (40-80 drops) three times daily. Capsules: 300-600 mg, 2-3 times daily. Takes several weeks to see benefits.', 'May interact with heart medications - consult healthcare provider. May cause mild digestive upset. Generally safe when used appropriately.'),
('Motherwort', 'Leonurus cardiaca', 'Motherwort supports cardiovascular health and helps with anxiety and stress. It has calming properties and supports women\'s health.', '/images/herbs/motherwort.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 10-15 minutes. Tincture: 2-4 ml three times daily.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml (40-80 drops) three times daily. Best taken consistently for several weeks.', 'Not recommended during pregnancy - may stimulate uterine contractions. May interact with blood-thinning medications. Generally safe for most adults.'),
('Yarrow', 'Achillea millefolium', 'Yarrow is a versatile herb that supports wound healing, reduces bleeding, and helps with fevers. It has astringent and anti-inflammatory properties.', '/images/herbs/yarrow.jpg', 'Tea: Steep 1-2 teaspoons of dried flowers in hot water for 10-15 minutes. Tincture: 2-4 ml three times daily. Topical: Use as compress for wounds.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml (40-80 drops) three times daily. Topical: Apply as needed to wounds.', 'May cause allergic reactions in people sensitive to ragweed. May increase bleeding risk. Not recommended during pregnancy.'),
('Calendula', 'Calendula officinalis', 'Calendula is excellent for skin healing and wound care. It has anti-inflammatory and antimicrobial properties that support skin health.', '/images/herbs/calendula.jpg', 'Topical: Apply cream, ointment, or infused oil to affected areas. Tea: Steep 1-2 teaspoons of flowers for internal use. Tincture: 2-4 ml daily.', 'Topical: Apply 2-3 times daily to wounds or skin irritation. Tea: 2-3 cups daily. Tincture: 2-4 ml daily.', 'May cause allergic reactions in people sensitive to ragweed or daisies. Generally very safe for topical use.'),
('Comfrey', 'Symphytum officinale', 'Comfrey is traditionally used for wound healing and bone support. It should only be used topically due to safety concerns with internal use.', '/images/herbs/comfrey.jpg', 'Topical only: Apply cream, ointment, or poultice to affected areas. Do not use internally.', 'Topical: Apply 2-3 times daily to wounds, bruises, or sprains. For external use only.', 'Do not use internally - contains compounds that can be toxic to the liver. Topical use should be limited to unbroken skin. Not recommended during pregnancy.'),
('Plantain', 'Plantago major', 'Plantain is a common weed with excellent wound-healing properties. It can be used fresh or dried for skin irritation and bug bites.', '/images/herbs/plantain.jpg', 'Fresh: Crush leaves and apply directly to skin. Tea: Steep 1-2 teaspoons of dried leaves. Tincture: 2-4 ml daily.', 'Topical: Apply fresh leaves as poultice as needed. Tea: 2-3 cups daily. Tincture: 2-4 ml daily.', 'Generally very safe. May cause mild allergic reactions in sensitive individuals.'),
('Eucalyptus', 'Eucalyptus globulus', 'Eucalyptus is excellent for respiratory support. It helps clear congestion and supports breathing. Essential oil is commonly used.', '/images/herbs/eucalyptus.jpg', 'Essential oil: Use in steam inhalation or diffuser. Tea: Steep 1-2 teaspoons of leaves. Topical: Dilute oil for chest rubs.', 'Essential oil: 2-4 drops in steam inhalation or diffuser. Tea: 2-3 cups daily. Topical: Always dilute - never use undiluted.', 'Essential oil is toxic if ingested. Keep away from children and pets. May cause skin irritation if not properly diluted.'),
('Thyme', 'Thymus vulgaris', 'Thyme has antimicrobial properties and supports respiratory health. It is excellent for coughs and respiratory infections.', '/images/herbs/thyme.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 10 minutes. Essential oil: Use in steam inhalation. Add to food.', 'Tea: 2-4 cups daily. Essential oil: 2-3 drops in steam inhalation. Culinary: Use liberally in cooking.', 'Essential oil should not be ingested. Generally safe in culinary amounts. May cause allergic reactions in sensitive individuals.'),
('Oregano', 'Origanum vulgare', 'Oregano has strong antimicrobial properties and supports immune health. It is rich in antioxidants and supports digestive health.', '/images/herbs/oregano.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves. Essential oil: Use in steam inhalation or dilute for topical use. Add to food.', 'Tea: 2-3 cups daily. Essential oil: 2-3 drops in steam inhalation. Culinary: Use in cooking regularly.', 'Essential oil is very strong and should be used sparingly. May cause skin irritation if not properly diluted. Generally safe in culinary amounts.'),
('Rosemary', 'Rosmarinus officinalis', 'Rosemary supports cognitive function and memory. It also has antimicrobial properties and supports circulation.', '/images/herbs/rosemary.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water. Essential oil: Use in aromatherapy. Add to food.', 'Tea: 2-3 cups daily. Essential oil: 2-4 drops in diffuser. Culinary: Use regularly in cooking.', 'May increase blood pressure in some individuals. Essential oil should not be ingested. Generally safe in culinary amounts.'),
('Sage', 'Salvia officinalis', 'Sage supports memory and cognitive function. It also has antimicrobial properties and is excellent for sore throats.', '/images/herbs/sage.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves for gargling or drinking. Essential oil: Use in steam inhalation. Add to food.', 'Tea: Gargle 2-3 times daily for sore throat, or drink 1-2 cups daily. Essential oil: 2-3 drops in steam. Culinary: Use in cooking.', 'Contains thujone - avoid high doses. Not recommended during pregnancy or breastfeeding. May lower blood sugar.'),
('Catnip', 'Nepeta cataria', 'Catnip is calming and supports digestive health. It is gentle and safe for children. It also helps with sleep and anxiety.', '/images/herbs/catnip.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 10 minutes. Tincture: 2-4 ml daily.', 'Tea: 1-3 cups daily, especially before bedtime. Tincture: 2-4 ml (40-80 drops) daily. Safe for children in smaller doses.', 'Generally very safe and gentle. May cause mild drowsiness. Safe for children when used appropriately.'),
('Lemon Balm', 'Melissa officinalis', 'Lemon balm is calming and supports mood. It helps with anxiety, sleep, and digestive issues. It has a pleasant lemon scent.', '/images/herbs/lemon-balm.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 10 minutes. Tincture: 2-4 ml daily. Fresh: Add to salads or drinks.', 'Tea: 2-4 cups daily, especially before bedtime. Tincture: 2-4 ml daily. Fresh: Use liberally in food and drinks.', 'May interact with thyroid medications. Generally very safe. May cause mild drowsiness.'),
('Passionflower', 'Passiflora incarnata', 'Passionflower is excellent for anxiety and sleep support. It has calming properties and helps promote restful sleep.', '/images/herbs/passionflower.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves and flowers in hot water for 10-15 minutes. Tincture: 2-4 ml before bedtime.', 'Tea: 1-2 cups 30-60 minutes before bedtime. Tincture: 2-4 ml (40-80 drops) before sleep. Can be combined with other calming herbs.', 'May cause drowsiness. May interact with sedatives and blood-thinning medications. Generally safe when used appropriately.'),
('Skullcap', 'Scutellaria lateriflora', 'Skullcap is a nervine herb that supports the nervous system. It helps with anxiety, stress, and promotes calm.', '/images/herbs/skullcap.jpg', 'Tea: Steep 1-2 teaspoons of dried leaves in hot water for 10-15 minutes. Tincture: 2-4 ml three times daily.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml (40-80 drops) three times daily. Best taken consistently for several weeks.', 'May cause drowsiness. May interact with sedatives. Generally safe when used appropriately.'),
('Hops', 'Humulus lupulus', 'Hops is well-known for its sedative properties. It supports sleep and helps with anxiety and restlessness.', '/images/herbs/hops.jpg', 'Tea: Steep 1-2 teaspoons of dried flowers in hot water for 10 minutes. Tincture: 2-4 ml before bedtime. Pillow: Place dried hops in pillow.', 'Tea: 1 cup 30-60 minutes before bedtime. Tincture: 2-4 ml (40-80 drops) before sleep. Pillow: Place in sachet near bed.', 'May cause drowsiness. Not recommended during pregnancy or if you have depression. May interact with sedatives.'),
('Black Cohosh', 'Actaea racemosa', 'Black cohosh is primarily used for women\'s health, particularly for menopausal symptoms. It supports hormonal balance.', '/images/herbs/black-cohosh.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Extract: Standardized extract is preferred.', 'Capsules: 20-40 mg standardized extract, 2 times daily. Tincture: 2-4 ml daily. Use for 6 months or less.', 'May interact with hormone medications. May cause liver issues in rare cases. Not recommended during pregnancy. Monitor liver function with extended use.'),
('Dong Quai', 'Angelica sinensis', 'Dong quai is a traditional Chinese herb for women\'s health. It supports menstrual health and hormonal balance.', '/images/herbs/dong-quai.jpg', 'Tea: Simmer 1-2 teaspoons of root in water for 15-20 minutes. Tincture: 2-4 ml daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml daily. Capsules: 500-1000 mg daily. Best used in cycles with menstrual cycle.', 'May increase bleeding risk. Not recommended during pregnancy. May interact with blood-thinning medications. May cause photosensitivity.'),
('Vitex', 'Vitex agnus-castus', 'Vitex, also known as chasteberry, supports women\'s hormonal health. It helps balance hormones and supports menstrual health.', '/images/herbs/vitex.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Extract: Standardized extract is preferred.', 'Capsules: 200-400 mg standardized extract daily. Tincture: 2-4 ml daily. Takes 3-6 months to see full benefits.', 'May interact with hormone medications and birth control. Not recommended during pregnancy. May cause mild digestive upset.'),
('Maca', 'Lepidium meyenii', 'Maca is an adaptogenic root that supports energy, stamina, and hormonal balance. It is particularly popular for libido support.', '/images/herbs/maca.jpg', 'Powder: Mix 1-2 teaspoons in smoothies, drinks, or food. Capsules: Follow product instructions.', 'Powder: 1-3 teaspoons (3-9 grams) daily. Capsules: 1500-3000 mg daily. Best taken consistently for several weeks.', 'May affect hormone levels. Use cautiously if you have hormone-sensitive conditions. Generally safe for most adults.'),
('Tribulus', 'Tribulus terrestris', 'Tribulus is used to support libido and athletic performance. It may help with energy and vitality.', '/images/herbs/tribulus.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Extract: Standardized extract is preferred.', 'Capsules: 250-500 mg standardized extract, 2-3 times daily. Tincture: 2-4 ml daily. Use in cycles.', 'May interact with diabetes medications. May affect hormone levels. Not recommended during pregnancy.'),
('Horny Goat Weed', 'Epimedium grandiflorum', 'Horny goat weed is traditionally used to support libido and sexual function. It may also support bone health.', '/images/herbs/horny-goat-weed.jpg', 'Capsules: Follow product instructions. Tincture: 2-4 ml daily. Tea: Steep 1-2 teaspoons of leaves.', 'Capsules: 500-1000 mg daily. Tincture: 2-4 ml daily. Tea: 2-3 cups daily. Takes several weeks to see benefits.', 'May interact with blood-thinning medications. May cause dizziness or dry mouth. Not recommended during pregnancy.'),
('Shatavari', 'Asparagus racemosus', 'Shatavari is an Ayurvedic herb that supports women\'s health, particularly reproductive health and lactation. It is adaptogenic.', '/images/herbs/shatavari.jpg', 'Powder: Mix 1-2 teaspoons in warm milk or water. Capsules: Follow product instructions. Tincture: 2-4 ml daily.', 'Powder: 1-3 grams daily. Capsules: 500-1000 mg daily. Tincture: 2-4 ml daily. Best taken consistently.', 'May affect hormone levels. Use cautiously if you have hormone-sensitive conditions. Generally safe for most adults.'),
('Reishi Mushroom', 'Ganoderma lucidum', 'Reishi is a medicinal mushroom known as the "mushroom of immortality." It supports immune function and overall wellness.', '/images/herbs/reishi.jpg', 'Tea: Simmer 1-2 teaspoons of dried mushroom slices for 1-2 hours. Capsules: Follow product instructions. Extract: Tincture or powder.', 'Tea: 2-3 cups daily. Capsules: 1-3 grams daily. Extract: Follow product instructions. Best taken consistently.', 'May interact with immunosuppressant medications. May cause dizziness or digestive upset in some. Generally safe.'),
('Chaga Mushroom', 'Inonotus obliquus', 'Chaga is a powerful medicinal mushroom rich in antioxidants. It supports immune function and overall health.', '/images/herbs/chaga.jpg', 'Tea: Simmer 1-2 teaspoons of chunks for 1-2 hours. Powder: Mix in drinks. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Powder: 1-2 grams daily. Capsules: 500-1000 mg daily. Best taken consistently.', 'May interact with blood-thinning medications. May interact with diabetes medications. Generally safe when used appropriately.'),
('Lion\'s Mane', 'Hericium erinaceus', 'Lion\'s Mane is a medicinal mushroom that supports cognitive function and nerve health. It may help with memory and focus.', '/images/herbs/lions-mane.jpg', 'Capsules: Follow product instructions. Tea: Simmer dried mushroom. Powder: Mix in drinks or food.', 'Capsules: 500-3000 mg daily. Tea: 2-3 cups daily. Powder: 1-3 grams daily. Takes several weeks to see benefits.', 'May interact with blood-thinning medications. Generally safe. May cause mild digestive upset in some individuals.'),
('Cordyceps', 'Cordyceps sinensis', 'Cordyceps is a medicinal mushroom that supports energy, athletic performance, and respiratory health. It is adaptogenic.', '/images/herbs/cordyceps.jpg', 'Capsules: Follow product instructions. Tea: Simmer dried cordyceps. Powder: Mix in drinks.', 'Capsules: 1-3 grams daily. Tea: 2-3 cups daily. Powder: 1-3 grams daily. Best taken consistently for several weeks.', 'May interact with immunosuppressant medications. May affect blood sugar. Generally safe when used appropriately.'),
('Schisandra', 'Schisandra chinensis', 'Schisandra is an adaptogenic berry that supports energy, mental performance, and liver health. It helps the body adapt to stress.', '/images/herbs/schisandra.jpg', 'Tea: Steep 1-2 teaspoons of dried berries in hot water. Tincture: 2-4 ml daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml daily. Capsules: 500-2000 mg daily. Best taken consistently.', 'May interact with medications metabolized by the liver. May cause mild digestive upset. Generally safe for most adults.'),
('Astragalus', 'Astragalus membranaceus', 'Astragalus is an adaptogenic herb that supports immune function and overall vitality. It helps the body resist stress and illness.', '/images/herbs/astragalus.jpg', 'Tea: Simmer 1-2 teaspoons of sliced root for 20-30 minutes. Tincture: 2-4 ml daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml daily. Capsules: 500-1000 mg daily. Best taken consistently for immune support.', 'May interact with immunosuppressant medications. May interact with blood-thinning medications. Generally safe when used appropriately.'),
('Rehmannia', 'Rehmannia glutinosa', 'Rehmannia is a traditional Chinese herb that supports kidney health and hormonal balance. It is nourishing and tonifying.', '/images/herbs/rehmannia.jpg', 'Tea: Simmer 1-2 teaspoons of root for 20-30 minutes. Tincture: 2-4 ml daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml daily. Capsules: 500-1000 mg daily. Best used in traditional formulas.', 'May interact with diabetes medications. May affect blood sugar. Generally safe when used appropriately.'),
('He Shou Wu', 'Polygonum multiflorum', 'He Shou Wu is a traditional Chinese herb that supports hair health, liver function, and overall vitality. It is considered a longevity herb.', '/images/herbs/he-shou-wu.jpg', 'Tea: Simmer 1-2 teaspoons of root for 20-30 minutes. Tincture: 2-4 ml daily. Capsules: Follow product instructions.', 'Tea: 2-3 cups daily. Tincture: 2-4 ml daily. Capsules: 500-1000 mg daily. Takes several months to see benefits.', 'May cause liver toxicity in some individuals - monitor liver function. May interact with liver medications. Use processed/steamed form when possible.');

-- Link Herbs to Conditions
INSERT INTO herbs_conditions (herb_id, condition_id, effectiveness_note) VALUES
-- Chamomile
(1, 1, 'Highly effective for stress and anxiety'),
(1, 2, 'Promotes restful sleep'),
(1, 3, 'Soothes digestive issues'),
-- Peppermint
(2, 3, 'Excellent for digestive problems'),
(2, 4, 'Can help relieve tension headaches'),
-- Ginger
(3, 5, 'Very effective for nausea'),
(3, 6, 'Strong anti-inflammatory properties'),
(3, 3, 'Supports digestive health'),
-- Turmeric
(4, 6, 'Powerful anti-inflammatory'),
(4, 7, 'Helps with joint pain and arthritis'),
-- Lavender
(5, 8, 'Calming for anxiety'),
(5, 2, 'Promotes better sleep'),
(5, 11, 'Topical use for skin conditions'),
-- Echinacea
(6, 9, 'Boosts immune system'),
(6, 14, 'Shortens cold and flu duration'),
-- Garlic
(7, 10, 'Supports cardiovascular health'),
(7, 9, 'Immune system support'),
-- Aloe Vera
(8, 11, 'Excellent for skin healing'),
(8, 3, 'Supports digestive health when processed'),
-- Green Tea
(9, 12, 'Provides gentle energy boost'),
(9, 10, 'Cardiovascular support'),
-- Valerian
(10, 2, 'Strong sleep aid'),
(10, 8, 'Reduces anxiety'),
-- Cinnamon
(11, 13, 'Helps regulate blood sugar'),
(11, 6, 'Anti-inflammatory properties'),
-- Holy Basil
(12, 1, 'Adaptogenic stress support'),
(12, 9, 'Immune system support'),
-- Ashwagandha
(13, 1, 'Adaptogenic stress management'),
(13, 12, 'Supports energy and vitality'),
-- Dandelion
(14, 15, 'Liver health and detoxification'),
(14, 3, 'Digestive support'),
-- Elderberry
(15, 9, 'Strong immune support'),
(15, 14, 'Effective for cold and flu symptoms'),
-- Milk Thistle
(16, 15, 'Excellent liver support and protection'),
(16, 11, 'Supports skin health'),
-- St. John's Wort
(17, 1, 'Supports mood and emotional wellness'),
(17, 8, 'May help with mild to moderate depression'),
-- Ginkgo Biloba
(18, 12, 'Supports cognitive function and memory'),
(18, 10, 'Improves circulation'),
-- Ginseng
(19, 1, 'Adaptogenic stress support'),
(19, 12, 'Boosts energy and stamina'),
-- Rhodiola
(20, 1, 'Adaptogenic stress management'),
(20, 12, 'Combats fatigue and supports endurance'),
-- Licorice Root
(21, 3, 'Soothes digestive system'),
(21, 14, 'Supports respiratory health'),
-- Marshmallow Root
(22, 3, 'Soothes irritated digestive tissues'),
(22, 14, 'Supports respiratory comfort'),
-- Slippery Elm
(23, 3, 'Coats and protects digestive tract'),
(23, 14, 'Soothes throat irritation'),
-- Fennel
(24, 3, 'Relieves gas and bloating'),
(24, 5, 'May help with nausea'),
-- Fenugreek
(25, 13, 'Helps regulate blood sugar'),
(25, 3, 'Supports digestive health'),
-- Burdock Root
(26, 15, 'Supports liver detoxification'),
(26, 11, 'Helps with skin conditions'),
-- Nettle
(27, 14, 'Supports respiratory health and allergies'),
(27, 6, 'Anti-inflammatory properties'),
-- Red Clover
(28, 1, 'Supports women\'s hormonal balance'),
(28, 11, 'Supports skin health'),
-- Saw Palmetto
(29, 10, 'Supports prostate and urinary health'),
-- Cranberry
(30, 14, 'Supports urinary tract health'),
-- Uva Ursi
(30, 14, 'Supports urinary tract health'),
-- Bilberry
(31, 10, 'Supports eye health and vision'),
-- Hawthorn
(32, 10, 'Cardiovascular tonic and support'),
-- Motherwort
(33, 10, 'Supports heart health'),
(33, 8, 'Calming for anxiety'),
-- Yarrow
(34, 11, 'Supports wound healing'),
(34, 14, 'Helps with fevers'),
-- Calendula
(35, 11, 'Excellent for skin healing'),
(35, 11, 'Supports wound care'),
-- Plantain
(36, 11, 'Soothes skin irritation and bug bites'),
-- Eucalyptus
(37, 14, 'Clears respiratory congestion'),
-- Thyme
(38, 14, 'Supports respiratory health'),
(38, 9, 'Antimicrobial properties'),
-- Oregano
(39, 9, 'Strong antimicrobial support'),
(39, 3, 'Supports digestive health'),
-- Rosemary
(40, 12, 'Supports cognitive function and memory'),
(40, 10, 'Supports circulation'),
-- Sage
(41, 12, 'Supports memory and cognitive function'),
(41, 14, 'Soothes sore throats'),
-- Catnip
(42, 2, 'Promotes sleep'),
(42, 3, 'Soothes digestive issues'),
-- Lemon Balm
(43, 1, 'Calming for stress and anxiety'),
(43, 2, 'Promotes restful sleep'),
-- Passionflower
(44, 8, 'Reduces anxiety'),
(44, 2, 'Promotes sleep'),
-- Skullcap
(45, 8, 'Calming for anxiety'),
(45, 1, 'Supports nervous system'),
-- Hops
(46, 2, 'Strong sleep aid'),
(46, 8, 'Reduces anxiety and restlessness'),
-- Black Cohosh
(47, 1, 'Supports women\'s hormonal health'),
-- Dong Quai
(48, 1, 'Supports women\'s hormonal balance'),
-- Vitex
(49, 1, 'Supports women\'s hormonal health'),
-- Maca
(50, 12, 'Boosts energy and stamina'),
(50, 1, 'Adaptogenic support'),
-- Tribulus
(51, 12, 'Supports energy and vitality'),
-- Horny Goat Weed
(52, 12, 'Supports vitality'),
-- Shatavari
(53, 1, 'Supports women\'s reproductive health'),
-- Reishi
(54, 9, 'Supports immune function'),
(54, 1, 'Adaptogenic support'),
-- Chaga
(55, 9, 'Strong immune support'),
(55, 6, 'Rich in antioxidants'),
-- Lion\'s Mane
(56, 12, 'Supports cognitive function and memory'),
-- Cordyceps
(57, 12, 'Boosts energy and athletic performance'),
(57, 14, 'Supports respiratory health'),
-- Schisandra
(58, 1, 'Adaptogenic stress support'),
(58, 15, 'Supports liver health'),
-- Astragalus
(59, 9, 'Supports immune function'),
(59, 1, 'Adaptogenic support'),
-- Rehmannia
(60, 1, 'Supports hormonal balance'),
-- He Shou Wu
(61, 15, 'Supports liver function'),
(61, 11, 'Supports hair health');

-- Insert Contraindications
INSERT INTO contraindications (herb_id, warning_text, severity, category) VALUES
-- Chamomile
(1, 'May cause allergic reactions in people sensitive to ragweed, chrysanthemums, or related plants', 'medium', 'allergy'),
(1, 'Not recommended during pregnancy without medical supervision', 'high', 'pregnancy'),
(1, 'May interact with blood-thinning medications (warfarin)', 'high', 'medication_interaction'),
-- Peppermint
(2, 'May cause heartburn or worsen GERD symptoms in some individuals', 'low', 'digestive'),
(2, 'Essential oil should never be ingested undiluted', 'high', 'toxicity'),
(2, 'Not recommended for infants or young children', 'medium', 'age_restriction'),
-- Ginger
(3, 'May interact with blood-thinning medications', 'high', 'medication_interaction'),
(3, 'Use cautiously if you have gallstones', 'medium', 'medical_condition'),
(3, 'High doses may cause heartburn', 'low', 'digestive'),
-- Turmeric
(4, 'May interact with blood-thinning medications', 'high', 'medication_interaction'),
(4, 'May interact with diabetes medications', 'high', 'medication_interaction'),
(4, 'May increase risk of kidney stones in susceptible individuals', 'medium', 'medical_condition'),
-- Lavender
(5, 'Essential oil should not be ingested', 'high', 'toxicity'),
(5, 'May cause skin irritation if not properly diluted', 'medium', 'topical'),
-- Echinacea
(6, 'Not recommended for people with autoimmune disorders', 'high', 'medical_condition'),
(6, 'May interact with immunosuppressant medications', 'high', 'medication_interaction'),
(6, 'Avoid if allergic to ragweed', 'high', 'allergy'),
-- Garlic
(7, 'May increase bleeding risk, especially with blood-thinning medications', 'high', 'medication_interaction'),
(7, 'May cause digestive upset in some people', 'low', 'digestive'),
-- Aloe Vera
(8, 'Internal use of raw aloe latex can be dangerous - only use processed products', 'high', 'toxicity'),
(8, 'Not recommended during pregnancy', 'medium', 'pregnancy'),
-- Green Tea
(9, 'Contains caffeine - may cause insomnia, anxiety, or jitteriness', 'medium', 'caffeine'),
(9, 'May interact with certain medications', 'medium', 'medication_interaction'),
-- Valerian
(10, 'May cause drowsiness - do not drive or operate machinery', 'high', 'sedation'),
(10, 'May interact with sedatives and alcohol', 'high', 'medication_interaction'),
-- Cinnamon
(11, 'High doses may cause liver damage', 'high', 'toxicity'),
(11, 'May interact with diabetes medications', 'high', 'medication_interaction'),
-- Holy Basil
(12, 'May lower blood sugar - monitor if diabetic', 'medium', 'medical_condition'),
(12, 'May slow blood clotting', 'medium', 'medication_interaction'),
-- Ashwagandha
(13, 'May interact with thyroid medications', 'high', 'medication_interaction'),
(13, 'May lower blood sugar', 'medium', 'medical_condition'),
(13, 'Use cautiously if you have autoimmune conditions', 'high', 'medical_condition'),
-- Dandelion
(14, 'May cause allergic reactions in people sensitive to ragweed', 'medium', 'allergy'),
(14, 'May interact with diuretic medications', 'high', 'medication_interaction'),
-- Elderberry
(15, 'Raw berries are toxic - only use cooked or processed products', 'high', 'toxicity'),
(15, 'May interact with immunosuppressant medications', 'high', 'medication_interaction'),
-- Milk Thistle
(16, 'May cause mild digestive upset in some individuals', 'low', 'digestive'),
(16, 'May cause allergic reactions in people sensitive to ragweed', 'medium', 'allergy'),
-- St. John's Wort
(17, 'May interact with many medications including antidepressants, birth control, and blood thinners', 'high', 'medication_interaction'),
(17, 'Not recommended during pregnancy', 'high', 'pregnancy'),
(17, 'May cause photosensitivity - avoid excessive sun exposure', 'medium', 'photosensitivity'),
-- Ginkgo Biloba
(18, 'May increase bleeding risk - avoid before surgery', 'high', 'medication_interaction'),
(18, 'May interact with blood-thinning medications', 'high', 'medication_interaction'),
(18, 'May cause headaches or digestive upset', 'low', 'digestive'),
-- Ginseng
(19, 'May increase blood pressure', 'medium', 'medical_condition'),
(19, 'May interact with blood-thinning and diabetes medications', 'high', 'medication_interaction'),
(19, 'Not recommended during pregnancy', 'high', 'pregnancy'),
(19, 'May cause insomnia if taken late in day', 'medium', 'sleep'),
-- Rhodiola
(20, 'May cause jitteriness or insomnia if taken late in day', 'medium', 'sleep'),
(20, 'May interact with antidepressants', 'medium', 'medication_interaction'),
-- Licorice Root
(21, 'May raise blood pressure with long-term use', 'high', 'medical_condition'),
(21, 'Avoid if you have high blood pressure, heart disease, or kidney disease', 'high', 'medical_condition'),
-- Marshmallow Root
(22, 'May slow absorption of other medications - take 1 hour before or after', 'medium', 'medication_interaction'),
-- Slippery Elm
(23, 'May slow absorption of medications - take 1-2 hours before or after', 'medium', 'medication_interaction'),
-- Fennel
(24, 'May cause allergic reactions in people sensitive to celery or carrots', 'medium', 'allergy'),
(24, 'Use cautiously during pregnancy', 'medium', 'pregnancy'),
-- Fenugreek
(25, 'May lower blood sugar - monitor if diabetic', 'medium', 'medical_condition'),
(25, 'May interact with diabetes medications', 'high', 'medication_interaction'),
(25, 'Not recommended during pregnancy', 'high', 'pregnancy'),
-- Burdock Root
(26, 'May cause allergic reactions in people sensitive to ragweed', 'medium', 'allergy'),
(26, 'May interact with diuretic medications', 'medium', 'medication_interaction'),
-- Nettle
(27, 'Fresh plant can cause skin irritation - only use dried or cooked', 'high', 'topical'),
(27, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
-- Red Clover
(28, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
(28, 'Not recommended during pregnancy or breastfeeding', 'high', 'pregnancy'),
(28, 'May affect hormone-sensitive conditions', 'medium', 'medical_condition'),
-- Saw Palmetto
(29, 'May interact with hormone medications', 'medium', 'medication_interaction'),
(29, 'May affect blood clotting', 'medium', 'medication_interaction'),
(29, 'Not recommended for women or children', 'high', 'age_restriction'),
-- Cranberry
(30, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
-- Uva Ursi
(31, 'Not for long-term use - limit to 1 week', 'high', 'duration'),
(31, 'Not recommended during pregnancy or breastfeeding', 'high', 'pregnancy'),
(31, 'May cause stomach upset', 'low', 'digestive'),
-- Bilberry
(32, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
(32, 'May lower blood sugar slightly', 'low', 'medical_condition'),
-- Hawthorn
(33, 'May interact with heart medications - consult healthcare provider', 'high', 'medication_interaction'),
(33, 'May cause mild digestive upset', 'low', 'digestive'),
-- Motherwort
(34, 'Not recommended during pregnancy - may stimulate uterine contractions', 'high', 'pregnancy'),
(34, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
-- Yarrow
(35, 'May cause allergic reactions in people sensitive to ragweed', 'medium', 'allergy'),
(35, 'May increase bleeding risk', 'high', 'medication_interaction'),
(35, 'Not recommended during pregnancy', 'high', 'pregnancy'),
-- Calendula
(36, 'May cause allergic reactions in people sensitive to ragweed or daisies', 'medium', 'allergy'),
-- Comfrey
(37, 'Do not use internally - contains compounds toxic to the liver', 'high', 'toxicity'),
(37, 'Topical use should be limited to unbroken skin', 'high', 'topical'),
(37, 'Not recommended during pregnancy', 'high', 'pregnancy'),
-- Plantain
(38, 'May cause mild allergic reactions in sensitive individuals', 'low', 'allergy'),
-- Eucalyptus
(39, 'Essential oil is toxic if ingested', 'high', 'toxicity'),
(39, 'Keep away from children and pets', 'high', 'age_restriction'),
(39, 'May cause skin irritation if not properly diluted', 'medium', 'topical'),
-- Thyme
(40, 'Essential oil should not be ingested', 'high', 'toxicity'),
(40, 'May cause allergic reactions in sensitive individuals', 'low', 'allergy'),
-- Oregano
(41, 'Essential oil is very strong and should be used sparingly', 'high', 'toxicity'),
(41, 'May cause skin irritation if not properly diluted', 'medium', 'topical'),
-- Rosemary
(42, 'May increase blood pressure in some individuals', 'medium', 'medical_condition'),
(42, 'Essential oil should not be ingested', 'high', 'toxicity'),
-- Sage
(43, 'Contains thujone - avoid high doses', 'high', 'toxicity'),
(43, 'Not recommended during pregnancy or breastfeeding', 'high', 'pregnancy'),
(43, 'May lower blood sugar', 'medium', 'medical_condition'),
-- Catnip
(44, 'May cause mild drowsiness', 'low', 'sedation'),
-- Lemon Balm
(45, 'May interact with thyroid medications', 'medium', 'medication_interaction'),
(45, 'May cause mild drowsiness', 'low', 'sedation'),
-- Passionflower
(46, 'May cause drowsiness', 'high', 'sedation'),
(46, 'May interact with sedatives and blood-thinning medications', 'high', 'medication_interaction'),
-- Skullcap
(47, 'May cause drowsiness', 'medium', 'sedation'),
(47, 'May interact with sedatives', 'medium', 'medication_interaction'),
-- Hops
(48, 'May cause drowsiness', 'high', 'sedation'),
(48, 'Not recommended during pregnancy or if you have depression', 'high', 'pregnancy'),
(48, 'May interact with sedatives', 'high', 'medication_interaction'),
-- Black Cohosh
(49, 'May interact with hormone medications', 'high', 'medication_interaction'),
(49, 'May cause liver issues in rare cases - monitor liver function', 'high', 'medical_condition'),
(49, 'Not recommended during pregnancy', 'high', 'pregnancy'),
-- Dong Quai
(50, 'May increase bleeding risk', 'high', 'medication_interaction'),
(50, 'Not recommended during pregnancy', 'high', 'pregnancy'),
(50, 'May interact with blood-thinning medications', 'high', 'medication_interaction'),
(50, 'May cause photosensitivity', 'medium', 'photosensitivity'),
-- Vitex
(51, 'May interact with hormone medications and birth control', 'high', 'medication_interaction'),
(51, 'Not recommended during pregnancy', 'high', 'pregnancy'),
(51, 'May cause mild digestive upset', 'low', 'digestive'),
-- Maca
(52, 'May affect hormone levels', 'medium', 'medical_condition'),
(52, 'Use cautiously if you have hormone-sensitive conditions', 'medium', 'medical_condition'),
-- Tribulus
(53, 'May interact with diabetes medications', 'medium', 'medication_interaction'),
(53, 'May affect hormone levels', 'medium', 'medical_condition'),
(53, 'Not recommended during pregnancy', 'high', 'pregnancy'),
-- Horny Goat Weed
(54, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
(54, 'May cause dizziness or dry mouth', 'low', 'side_effect'),
(54, 'Not recommended during pregnancy', 'high', 'pregnancy'),
-- Shatavari
(55, 'May affect hormone levels', 'medium', 'medical_condition'),
(55, 'Use cautiously if you have hormone-sensitive conditions', 'medium', 'medical_condition'),
-- Reishi
(56, 'May interact with immunosuppressant medications', 'high', 'medication_interaction'),
(56, 'May cause dizziness or digestive upset in some', 'low', 'side_effect'),
-- Chaga
(57, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
(57, 'May interact with diabetes medications', 'medium', 'medication_interaction'),
-- Lion\'s Mane
(58, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
(58, 'May cause mild digestive upset in some individuals', 'low', 'digestive'),
-- Cordyceps
(59, 'May interact with immunosuppressant medications', 'medium', 'medication_interaction'),
(59, 'May affect blood sugar', 'medium', 'medical_condition'),
-- Schisandra
(60, 'May interact with medications metabolized by the liver', 'medium', 'medication_interaction'),
(60, 'May cause mild digestive upset', 'low', 'digestive'),
-- Astragalus
(61, 'May interact with immunosuppressant medications', 'high', 'medication_interaction'),
(61, 'May interact with blood-thinning medications', 'medium', 'medication_interaction'),
-- Rehmannia
(62, 'May interact with diabetes medications', 'medium', 'medication_interaction'),
(62, 'May affect blood sugar', 'medium', 'medical_condition'),
-- He Shou Wu
(63, 'May cause liver toxicity in some individuals - monitor liver function', 'high', 'medical_condition'),
(63, 'May interact with liver medications', 'high', 'medication_interaction');

