package pro.dite;

import org.eclipse.jgit.revwalk.RevCommit;

import java.io.*;
import java.util.HashSet;
import java.util.Hashtable;

public class Cache implements Serializable
{

    Hashtable<String, CacheEntry> entries = new Hashtable<String, CacheEntry>();

    public boolean contains(RevCommit commit)
    {
        return entries.containsKey(commit.getId().toString());
    }

    public void saveToFile(File file)
    {
        try
        {
            FileOutputStream fileOut = new FileOutputStream(file);
            ObjectOutputStream out = new ObjectOutputStream(fileOut);
            out.writeObject(this);
            out.close();
            fileOut.close();
        } catch (IOException i)
        {
            i.printStackTrace();
        }
    }

    static Cache loadFromFile(File file)
    {
        Cache cache = null;
        try
        {
            FileInputStream fileIn = new FileInputStream(file);
            ObjectInputStream in = new ObjectInputStream(fileIn);
            cache = (Cache) in.readObject();
            in.close();
            fileIn.close();
            return cache;
        } catch (IOException i)
        {
            return new Cache();
        } catch (ClassNotFoundException c)
        {
            // must never happen
            return new Cache();
        }
    }

}
